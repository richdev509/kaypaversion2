<?php

namespace App\Services\Business;

use App\Models\Business\BusinessCurrentAccount;
use App\Models\Business\BusinessCurrentAccountTransaction;
use App\Models\Business\BusinessSavingsAccount;
use Illuminate\Support\Facades\DB;

class BusinessAccountService
{
    private function assertBusinessOperational(int $businessId): void
    {
        $business = \App\Models\Business\BusinessEntity::findOrFail($businessId);

        if ($business->status !== 'active') {
            throw new \RuntimeException('Ce business est suspendu ou clôturé — opération impossible.');
        }

        if ($business->status_kyc !== 'verified') {
            throw new \RuntimeException('Le KYC de ce business n\'est pas encore approuvé par l\'administration — opération impossible.');
        }
    }

    public function deposit(
        BusinessCurrentAccount $account,
        float $amount,
        string $method,
        ?string $reference,
        ?string $note,
        int $createdBy
    ): BusinessCurrentAccountTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Le montant du dépôt doit être positif.');
        }

        return DB::transaction(function () use ($account, $amount, $method, $reference, $note, $createdBy) {
            $account = BusinessCurrentAccount::lockForUpdate()->findOrFail($account->id);

            $this->assertBusinessOperational($account->business_id);

            if (! $account->isActive()) {
                throw new \RuntimeException('Ce compte courant n\'est pas actif.');
            }

            $balanceAfter = (float) $account->balance + $amount;

            $account->update([
                'balance'      => $balanceAfter,
                'last_flux_at' => now(),
            ]);

            return BusinessCurrentAccountTransaction::create([
                'business_current_account_id' => $account->id,
                'account_number'              => $account->account_number,
                'business_id'                 => $account->business_id,
                'type'                        => BusinessCurrentAccountTransaction::TYPE_DEPOT,
                'amount'                      => $amount,
                'balance_after'               => $balanceAfter,
                'method'                      => $method,
                'reference'                   => $reference,
                'note'                        => $note,
                'created_by'                  => $createdBy,
            ]);
        });
    }

    public function withdraw(
        BusinessCurrentAccount $account,
        float $amount,
        string $method,
        ?string $reference,
        ?string $note,
        int $createdBy
    ): BusinessCurrentAccountTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Le montant du retrait doit être positif.');
        }

        return DB::transaction(function () use ($account, $amount, $method, $reference, $note, $createdBy) {
            $account = BusinessCurrentAccount::lockForUpdate()->findOrFail($account->id);

            $this->assertBusinessOperational($account->business_id);

            if (! $account->isActive()) {
                throw new \RuntimeException('Ce compte courant n\'est pas actif.');
            }

            if ((float) $account->balance < $amount) {
                throw new \RuntimeException('Solde insuffisant pour effectuer ce retrait.');
            }

            $balanceAfter = (float) $account->balance - $amount;

            $account->update([
                'balance'      => $balanceAfter,
                'last_flux_at' => now(),
            ]);

            return BusinessCurrentAccountTransaction::create([
                'business_current_account_id' => $account->id,
                'account_number'              => $account->account_number,
                'business_id'                 => $account->business_id,
                'type'                        => BusinessCurrentAccountTransaction::TYPE_RETRAIT,
                'amount'                      => $amount,
                'balance_after'               => $balanceAfter,
                'method'                      => $method,
                'reference'                   => $reference,
                'note'                        => $note,
                'created_by'                  => $createdBy,
            ]);
        });
    }

    public function depositToSavings(
        BusinessSavingsAccount $account,
        float $amount,
        ?string $note,
        int $createdBy
    ): BusinessSavingsAccount {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Le montant du dépôt doit être positif.');
        }

        return DB::transaction(function () use ($account, $amount, $note, $createdBy) {
            $account = BusinessSavingsAccount::lockForUpdate()->findOrFail($account->id);

            $this->assertBusinessOperational($account->business_id);

            if (! $account->isActive()) {
                throw new \RuntimeException('Ce compte épargne n\'est pas actif.');
            }

            $account->update([
                'balance' => (float) $account->balance + $amount,
            ]);

            return $account->fresh();
        });
    }

    public function withdrawFromSavings(
        BusinessSavingsAccount $account,
        float $amount,
        ?string $note,
        int $createdBy
    ): BusinessSavingsAccount {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Le montant du retrait doit être positif.');
        }

        return DB::transaction(function () use ($account, $amount, $note, $createdBy) {
            $account = BusinessSavingsAccount::lockForUpdate()->findOrFail($account->id);

            $this->assertBusinessOperational($account->business_id);

            if (! $account->isActive()) {
                throw new \RuntimeException('Ce compte épargne n\'est pas actif.');
            }

            if ((float) $account->balance < $amount) {
                throw new \RuntimeException('Solde insuffisant pour effectuer ce retrait.');
            }

            $account->update([
                'balance' => (float) $account->balance - $amount,
            ]);

            return $account->fresh();
        });
    }
}
