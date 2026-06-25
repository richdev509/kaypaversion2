<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\Client;
use App\Models\CurrentAccount;
use App\Models\CurrentAccountTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CurrentAccountService
{
    /**
     * Ouvrir un nouveau compte courant pour un client existant.
     * Prélève les frais d'ouverture configurés.
     */
    public function openAccount(Client $client, string $paymentMethod): CurrentAccount
    {
        $fraisOuverture = (float) AppSetting::get('cc_frais_ouverture', 200);

        return DB::transaction(function () use ($client, $paymentMethod, $fraisOuverture) {
            $user = Auth::user();

            $account = CurrentAccount::create([
                'client_id'  => $client->id,
                'branch_id'  => $user?->branch_id,
                'balance'    => 0,
                'status'     => 'actif',
                'created_by' => $user?->id,
            ]);

            // Enregistrer les frais d'ouverture (ne s'ajoutent pas au solde)
            CurrentAccountTransaction::create([
                'current_account_id'     => $account->id,
                'current_account_number' => $account->account_number,
                'client_id'              => $client->id,
                'type'                   => CurrentAccountTransaction::TYPE_FRAIS_OUVERTURE,
                'amount'                 => $fraisOuverture,
                'balance_after'          => 0,
                'method'                 => $paymentMethod,
                'reference'              => 'OUVERTURE-' . $account->account_number,
                'note'                   => "Frais d'ouverture compte courant ({$fraisOuverture} GDS)",
                'created_by'             => $user?->id,
            ]);

            return $account;
        });
    }

    /**
     * Effectuer un dépôt sur un compte courant.
     */
    public function deposit(CurrentAccount $account, float $amount, string $method, ?string $note = null): CurrentAccountTransaction
    {
        return DB::transaction(function () use ($account, $amount, $method, $note) {
            // Verrouiller la ligne pour éviter les dépôts concurrents
            $fresh = CurrentAccount::lockForUpdate()->findOrFail($account->id);

            $newBalance = $fresh->balance + $amount;

            $tx = CurrentAccountTransaction::create([
                'current_account_id'     => $fresh->id,
                'current_account_number' => $fresh->account_number,
                'client_id'              => $fresh->client_id,
                'type'                   => CurrentAccountTransaction::TYPE_DEPOT,
                'amount'                 => $amount,
                'balance_after'          => $newBalance,
                'method'                 => $method,
                'reference'              => 'DEP-' . strtoupper(uniqid()),
                'note'                   => $note,
                'created_by'             => Auth::id(),
            ]);

            $fresh->update(['balance' => $newBalance]);
            $account->balance = $newBalance;

            return $tx;
        });
    }

    /**
     * Effectuer un retrait sur un compte courant.
     */
    public function withdraw(CurrentAccount $account, float $amount, string $method, ?string $note = null): CurrentAccountTransaction
    {
        return DB::transaction(function () use ($account, $amount, $method, $note) {
            // Verrouiller la ligne et re-lire le solde réel (évite race condition)
            $fresh = CurrentAccount::lockForUpdate()->findOrFail($account->id);

            if ($amount > $fresh->balance) {
                throw new \RuntimeException('Solde insuffisant pour effectuer ce retrait.');
            }

            $newBalance = $fresh->balance - $amount;

            $tx = CurrentAccountTransaction::create([
                'current_account_id'     => $fresh->id,
                'current_account_number' => $fresh->account_number,
                'client_id'              => $fresh->client_id,
                'type'                   => CurrentAccountTransaction::TYPE_RETRAIT,
                'amount'                 => $amount,
                'balance_after'          => $newBalance,
                'method'                 => $method,
                'reference'              => 'RET-' . strtoupper(uniqid()),
                'note'                   => $note,
                'created_by'             => Auth::id(),
            ]);

            $fresh->update(['balance' => $newBalance]);
            $account->balance = $newBalance;

            return $tx;
        });
    }

    /**
     * Prélever le frais de service mensuel sur un compte actif.
     * Appelé par la commande planifiée.
     */
    public function applyMonthlyFee(CurrentAccount $account): ?CurrentAccountTransaction
    {
        if (!$account->isActive() || !$account->needsMonthlyFee()) {
            return null;
        }

        $frais = (float) AppSetting::get('cc_frais_service_mensuel', 10);

        return DB::transaction(function () use ($account, $frais) {
            $newBalance = $account->balance - $frais;

            $tx = CurrentAccountTransaction::create([
                'current_account_id'     => $account->id,
                'current_account_number' => $account->account_number,
                'client_id'              => $account->client_id,
                'type'                   => CurrentAccountTransaction::TYPE_FRAIS_SERVICE,
                'amount'                 => $frais,
                'balance_after'          => $newBalance,
                'method'                 => 'system',
                'reference'              => 'FRAIS-' . now()->format('Ym') . '-' . $account->account_number,
                'note'                   => 'Frais de service mensuel — ' . now()->translatedFormat('F Y'),
                'created_by'             => null,
            ]);

            $account->update([
                'balance'             => $newBalance,
                'last_fee_charged_at' => now(),
            ]);

            return $tx;
        });
    }
}
