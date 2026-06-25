<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Client;
use App\Models\SavingsAccount;
use App\Models\SavingsAccountTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingsAccountService
{
    public function openAccount(Client $client, string $paymentMethod, float $initialDeposit): SavingsAccount
    {
        $fraisOuverture = (float) AppSetting::get('sce_frais_ouverture', 0);
        $soldeMinimum   = (float) AppSetting::get('sce_solde_minimum', 500);

        if ($initialDeposit < $soldeMinimum) {
            throw new \RuntimeException("Le dépôt initial doit être d'au moins {$soldeMinimum} GDS.");
        }

        return DB::transaction(function () use ($client, $paymentMethod, $fraisOuverture, $initialDeposit) {
            $user = Auth::user();

            $account = SavingsAccount::create([
                'client_id'  => $client->id,
                'branch_id'  => $user?->branch_id,
                'balance'    => 0,
                'status'     => 'actif',
                'created_by' => $user?->id,
            ]);

            // Frais d'ouverture seulement si > 0
            if ($fraisOuverture > 0) {
                SavingsAccountTransaction::create([
                    'savings_account_id'     => $account->id,
                    'savings_account_number' => $account->account_number,
                    'client_id'              => $client->id,
                    'type'                   => SavingsAccountTransaction::TYPE_FRAIS_OUVERTURE,
                    'amount'                 => $fraisOuverture,
                    'balance_after'          => 0,
                    'method'                 => $paymentMethod,
                    'reference'              => 'OUVERTURE-' . $account->account_number,
                    'note'                   => "Frais d'ouverture compte épargne ({$fraisOuverture} GDS)",
                    'created_by'             => $user?->id,
                ]);
            }

            // Dépôt initial obligatoire
            SavingsAccountTransaction::create([
                'savings_account_id'     => $account->id,
                'savings_account_number' => $account->account_number,
                'client_id'              => $client->id,
                'type'                   => SavingsAccountTransaction::TYPE_DEPOT,
                'amount'                 => $initialDeposit,
                'balance_after'          => $initialDeposit,
                'method'                 => $paymentMethod,
                'reference'              => 'DEPOT-INIT-' . $account->account_number,
                'note'                   => 'Dépôt initial à l\'ouverture du compte',
                'created_by'             => $user?->id,
            ]);

            $account->update(['balance' => $initialDeposit]);

            return $account;
        });
    }

    public function deposit(SavingsAccount $account, float $amount, string $method, ?string $note = null): SavingsAccountTransaction
    {
        return DB::transaction(function () use ($account, $amount, $method, $note) {
            $fresh = SavingsAccount::lockForUpdate()->findOrFail($account->id);

            $newBalance = $fresh->balance + $amount;

            $tx = SavingsAccountTransaction::create([
                'savings_account_id'     => $fresh->id,
                'savings_account_number' => $fresh->account_number,
                'client_id'              => $fresh->client_id,
                'type'                   => SavingsAccountTransaction::TYPE_DEPOT,
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

    public function withdraw(SavingsAccount $account, float $amount, string $method, ?string $note = null): SavingsAccountTransaction
    {
        return DB::transaction(function () use ($account, $amount, $method, $note) {
            $fresh        = SavingsAccount::lockForUpdate()->findOrFail($account->id);
            $soldeMinimum = (float) AppSetting::get('sce_solde_minimum', 500);

            if ($amount > $fresh->balance) {
                throw new \RuntimeException('Solde insuffisant pour effectuer ce retrait.');
            }

            if (($fresh->balance - $amount) < $soldeMinimum) {
                throw new \RuntimeException(
                    "Le solde ne peut pas descendre en dessous de {$soldeMinimum} GDS. " .
                    "Montant maximum retirable : " . number_format($fresh->balance - $soldeMinimum, 2) . " GDS."
                );
            }

            $newBalance = $fresh->balance - $amount;

            $tx = SavingsAccountTransaction::create([
                'savings_account_id'     => $fresh->id,
                'savings_account_number' => $fresh->account_number,
                'client_id'              => $fresh->client_id,
                'type'                   => SavingsAccountTransaction::TYPE_RETRAIT,
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

    public function applyMonthlyInterest(SavingsAccount $account): ?SavingsAccountTransaction
    {
        if (!$account->isActive() || !$account->needsMonthlyInterest()) {
            return null;
        }

        $tauxMensuel   = (float) AppSetting::get('sce_taux_interet_mensuel', 0.5);
        $soldeMinimum  = (float) AppSetting::get('sce_solde_minimum_interet', 500);

        // Pas d'intérêt si solde inférieur au minimum configuré
        if ($account->balance < $soldeMinimum) {
            $account->update(['last_interest_at' => now()]);
            return null;
        }

        $interet = round($account->balance * ($tauxMensuel / 100), 2);

        if ($interet <= 0) {
            return null;
        }

        return DB::transaction(function () use ($account, $interet) {
            $fresh      = SavingsAccount::lockForUpdate()->findOrFail($account->id);
            $newBalance = $fresh->balance + $interet;

            $tx = SavingsAccountTransaction::create([
                'savings_account_id'     => $fresh->id,
                'savings_account_number' => $fresh->account_number,
                'client_id'              => $fresh->client_id,
                'type'                   => SavingsAccountTransaction::TYPE_INTERET,
                'amount'                 => $interet,
                'balance_after'          => $newBalance,
                'method'                 => 'system',
                'reference'              => 'INT-' . now()->format('Ym') . '-' . $fresh->account_number,
                'note'                   => 'Intérêt mensuel — ' . now()->translatedFormat('F Y'),
                'created_by'             => null,
            ]);

            $fresh->update([
                'balance'          => $newBalance,
                'last_interest_at' => now(),
            ]);

            return $tx;
        });
    }
}
