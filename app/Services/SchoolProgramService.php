<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Client;
use App\Models\CurrentAccount;
use App\Models\SavingsAccount;
use App\Models\SchoolProgram;
use App\Models\SchoolProgramEnrollment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolProgramService
{
    public function checkEligibility(Client $client, SchoolProgram $program): array
    {
        $hasCurrentAccount = CurrentAccount::where('client_id', $client->id)
            ->where('status', 'actif')
            ->exists();

        if (! $hasCurrentAccount) {
            return ['eligible' => false, 'reason' => 'Le client n\'a pas de compte courant actif.', 'tier' => null, 'savings_account' => null];
        }

        $savingsAccount = SavingsAccount::where('client_id', $client->id)
            ->where('status', 'actif')
            ->first();

        if (! $savingsAccount) {
            return ['eligible' => false, 'reason' => 'Le client n\'a pas de compte épargne actif.', 'tier' => null, 'savings_account' => null];
        }

        if ($savingsAccount->balance < $program->tier1_seuil) {
            return ['eligible' => false, 'reason' => "Solde épargne insuffisant (minimum {$program->tier1_seuil} GDS requis).", 'tier' => null, 'savings_account' => $savingsAccount];
        }

        $alreadyEnrolled = SchoolProgramEnrollment::where('school_program_id', $program->id)
            ->where('client_id', $client->id)
            ->exists();

        if ($alreadyEnrolled) {
            return ['eligible' => false, 'reason' => 'Le client est déjà inscrit à ce programme.', 'tier' => null, 'savings_account' => $savingsAccount];
        }

        $tier = $this->determineTier($savingsAccount, $program);

        return ['eligible' => true, 'reason' => null, 'tier' => $tier, 'savings_account' => $savingsAccount];
    }

    public function enroll(Client $client, SchoolProgram $program, ?User $enrolledBy = null): SchoolProgramEnrollment
    {
        return DB::transaction(function () use ($client, $program, $enrolledBy) {
            $eligibility = $this->checkEligibility($client, $program);

            if (! $eligibility['eligible']) {
                throw new \RuntimeException($eligibility['reason']);
            }

            $account = SavingsAccount::lockForUpdate()->findOrFail($eligibility['savings_account']->id);

            $soldeDisponible = (float) $account->balance - (float) $account->balance_blocked;

            if ($soldeDisponible < (float) $program->montant_blocage) {
                throw new \RuntimeException(
                    "Solde disponible insuffisant pour le blocage requis. Disponible : " .
                    number_format($soldeDisponible, 2) . " GDS, requis : " .
                    number_format($program->montant_blocage, 2) . " GDS."
                );
            }

            $tier = $this->determineTier($account, $program);
            $couponValue = $tier === 2 ? $program->tier2_coupon : $program->tier1_coupon;
            $couponCode = $this->generateCouponCode();
            $blockedUntil = now()->addDays($program->duree_blocage_jours);

            $account->increment('balance_blocked', (float) $program->montant_blocage);

            return SchoolProgramEnrollment::create([
                'school_program_id'   => $program->id,
                'client_id'           => $client->id,
                'savings_account_id'  => $account->id,
                'coupon_code'         => $couponCode,
                'coupon_value'        => $couponValue,
                'tier'                => $tier,
                'coupon_status'       => 'active',
                'balance_blocked'     => $program->montant_blocage,
                'blocked_until'       => $blockedUntil,
                'enrolled_by'         => $enrolledBy?->id,
            ]);
        });
    }

    public function bulkEnroll(SchoolProgram $program, ?User $enrolledBy = null): array
    {
        $clients = $this->getEligibleClients($program);
        $enrolled = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($clients as $client) {
            try {
                $this->enroll($client, $program, $enrolledBy);
                $enrolled++;
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "[{$client->client_id}] {$client->first_name} {$client->last_name} : {$e->getMessage()}";
            }
        }

        return compact('enrolled', 'skipped', 'errors');
    }

    public function getEligibleClients(SchoolProgram $program): Collection
    {
        $alreadyEnrolled = SchoolProgramEnrollment::where('school_program_id', $program->id)
            ->pluck('client_id');

        $eligibleSavingsAccounts = SavingsAccount::where('status', 'actif')
            ->where('balance', '>=', $program->tier1_seuil)
            ->whereNotIn('client_id', $alreadyEnrolled)
            ->pluck('client_id');

        $clientsWithCurrentAccount = CurrentAccount::where('status', 'actif')
            ->whereIn('client_id', $eligibleSavingsAccounts)
            ->pluck('client_id');

        $clients = Client::whereIn('id', $clientsWithCurrentAccount)
            ->orderBy('last_name')
            ->get();

        // Attach the active savings account to each client as a transient property
        $accounts = SavingsAccount::where('status', 'actif')
            ->whereIn('client_id', $clientsWithCurrentAccount)
            ->get()
            ->keyBy('client_id');

        foreach ($clients as $client) {
            $client->setRelation('activeSavingsAccount', $accounts->get($client->id));
        }

        return $clients;
    }

    public function verifyCoupon(string $couponCode): array
    {
        $enrollment = SchoolProgramEnrollment::with(['program', 'client'])
            ->where('coupon_code', strtoupper(trim($couponCode)))
            ->first();

        if (! $enrollment) {
            return ['valid' => false, 'enrollment' => null, 'message' => 'Code coupon introuvable.'];
        }

        if ($enrollment->coupon_status !== 'active') {
            $label = $enrollment->getStatusLabel();
            return ['valid' => false, 'enrollment' => $enrollment, 'message' => "Coupon non valide — statut : {$label}."];
        }

        if (! $enrollment->program->isCouponPeriodActive()) {
            return ['valid' => false, 'enrollment' => $enrollment, 'message' => 'Le programme associé à ce coupon n\'est pas actif actuellement.'];
        }

        return ['valid' => true, 'enrollment' => $enrollment, 'message' => 'Coupon valide.'];
    }

    public function useCoupon(string $couponCode, string $codeParrain): SchoolProgramEnrollment
    {
        return DB::transaction(function () use ($couponCode, $codeParrain) {
            $affiliate = Affiliate::where('code_parrain', strtoupper(trim($codeParrain)))
                ->where('status', 'approuve')
                ->first();

            if (! $affiliate) {
                throw new \RuntimeException('Code parrain invalide ou affilié non approuvé.');
            }

            $enrollment = SchoolProgramEnrollment::lockForUpdate()
                ->where('coupon_code', strtoupper(trim($couponCode)))
                ->where('coupon_status', 'active')
                ->first();

            if (! $enrollment) {
                throw new \RuntimeException('Coupon introuvable ou déjà utilisé/expiré.');
            }

            $enrollment->update([
                'coupon_status'        => 'used',
                'used_at'              => now(),
                'used_by_affiliate_id' => $affiliate->id,
            ]);

            return $enrollment->fresh(['program', 'client', 'usedByAffiliate']);
        });
    }

    public function releaseExpiredBlocks(): int
    {
        $enrollments = SchoolProgramEnrollment::where('coupon_status', 'active')
            ->where('blocked_until', '<=', now())
            ->where('balance_blocked', '>', 0)
            ->get();

        $count = 0;

        foreach ($enrollments as $enrollment) {
            DB::transaction(function () use ($enrollment, &$count) {
                $account = SavingsAccount::lockForUpdate()->find($enrollment->savings_account_id);

                if ($account) {
                    $toRelease = min((float) $enrollment->balance_blocked, (float) $account->balance_blocked);
                    if ($toRelease > 0) {
                        $account->decrement('balance_blocked', $toRelease);
                    }
                }

                $enrollment->update(['balance_blocked' => 0]);
                $count++;
            });
        }

        return $count;
    }

    private function generateCouponCode(): string
    {
        do {
            $code = 'SCOL-' . strtoupper(Str::random(8));
        } while (SchoolProgramEnrollment::where('coupon_code', $code)->exists());

        return $code;
    }

    private function determineTier(SavingsAccount $account, SchoolProgram $program): int
    {
        if ((float) $account->balance >= (float) $program->tier2_seuil) {
            return 2;
        }
        return 1;
    }
}
