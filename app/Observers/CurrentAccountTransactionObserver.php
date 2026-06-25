<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\CurrentAccountTransaction;
use Illuminate\Support\Facades\Log;

class CurrentAccountTransactionObserver
{
    public function created(CurrentAccountTransaction $transaction): void
    {
        try {
            // Résoudre la branche depuis le compte (plus fiable que via le créateur)
            $branchId = $transaction->currentAccount?->branch_id;
            $branch   = $branchId ? Branch::find($branchId) : null;

            // Mise à jour caisse physique de la succursale
            if ($branch) {
                match ($transaction->type) {
                    // Argent physique entre dans la caisse
                    CurrentAccountTransaction::TYPE_DEPOT,
                    CurrentAccountTransaction::TYPE_FRAIS_OUVERTURE => $branch->increment('cash_balance', $transaction->amount),

                    // Argent physique sort de la caisse
                    CurrentAccountTransaction::TYPE_RETRAIT => $branch->decrement('cash_balance', $transaction->amount),

                    // Frais service = déduction virtuelle, pas de mouvement physique
                    default => null,
                };
            }

            // Log systématique de toutes les opérations
            $branchName = $branch?->name ?? 'Sans succursale';
            $createdBy  = $transaction->created_by ?? 'système';

            Log::info('CC_TRANSACTION', [
                'transaction_id'  => $transaction->transaction_id,
                'account'         => $transaction->current_account_number,
                'type'            => $transaction->type,
                'amount'          => $transaction->amount,
                'balance_after'   => $transaction->balance_after,
                'method'          => $transaction->method,
                'reference'       => $transaction->reference,
                'branch'          => $branchName,
                'created_by'      => $createdBy,
            ]);

        } catch (\Exception $e) {
            Log::error('CC_TRANSACTION_OBSERVER_ERROR', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
