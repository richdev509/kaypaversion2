<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\SavingsAccountTransaction;
use Illuminate\Support\Facades\Log;

class SavingsAccountTransactionObserver
{
    public function created(SavingsAccountTransaction $transaction): void
    {
        try {
            $branchId = $transaction->savingsAccount?->branch_id;
            $branch   = $branchId ? Branch::find($branchId) : null;

            if ($branch) {
                match ($transaction->type) {
                    // Argent physique entre dans la caisse
                    SavingsAccountTransaction::TYPE_DEPOT,
                    SavingsAccountTransaction::TYPE_FRAIS_OUVERTURE => $branch->increment('cash_balance', $transaction->amount),

                    // Argent physique sort de la caisse
                    SavingsAccountTransaction::TYPE_RETRAIT => $branch->decrement('cash_balance', $transaction->amount),

                    // Intérêt = crédit virtuel, pas de mouvement physique
                    default => null,
                };
            }

            Log::info('SCE_TRANSACTION', [
                'transaction_id' => $transaction->transaction_id,
                'account'        => $transaction->savings_account_number,
                'type'           => $transaction->type,
                'amount'         => $transaction->amount,
                'balance_after'  => $transaction->balance_after,
                'method'         => $transaction->method,
                'reference'      => $transaction->reference,
                'branch'         => $branch?->name ?? 'Sans succursale',
                'created_by'     => $transaction->created_by ?? 'système',
            ]);

        } catch (\Exception $e) {
            Log::error('SCE_TRANSACTION_OBSERVER_ERROR', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}
