<?php

namespace App\Observers;

use App\Models\TransactionOnline;
use App\Models\BalancePaiementOnline;
use Illuminate\Support\Facades\Log;

class TransactionOnlineObserver
{
    /**
     * Handle the TransactionOnline "created" event.
     */
    public function created(TransactionOnline $transaction): void
    {
        // Ne mettre à jour la balance que pour les transactions réussies
        if ($transaction->statut === 'reussie') {
            $this->updateBalance($transaction);
        }
    }

    /**
     * Handle the TransactionOnline "updated" event.
     */
    public function updated(TransactionOnline $transaction): void
    {
        // Si le statut change vers "reussie", mettre à jour la balance
        if ($transaction->wasChanged('statut') && $transaction->statut === 'reussie') {
            $this->updateBalance($transaction);
        }

        // Si le statut change de "reussie" vers autre chose, inverser la transaction
        if ($transaction->wasChanged('statut') && $transaction->getOriginal('statut') === 'reussie' && $transaction->statut !== 'reussie') {
            $this->reverseBalance($transaction);
        }
    }

    /**
     * Mettre à jour la balance après une transaction
     */
    private function updateBalance(TransactionOnline $transaction): void
    {
        try {
            $balance = BalancePaiementOnline::getSolde();
            $balance->updateAfterTransaction($transaction->type, $transaction->montant);

            Log::info('Balance mise à jour après transaction', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'nouvelle_balance' => $balance->balance,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la balance', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Inverser la balance après annulation d'une transaction
     */
    private function reverseBalance(TransactionOnline $transaction): void
    {
        try {
            $balance = BalancePaiementOnline::getSolde();

            switch ($transaction->type) {
                case 'depot':
                    $balance->balance -= $transaction->montant;
                    $balance->total_depot -= $transaction->montant;
                    break;

                case 'retrait':
                    $balance->balance += $transaction->montant;
                    $balance->total_retrait -= $transaction->montant;
                    break;

                case 'ouverture':
                    $balance->balance -= $transaction->montant;
                    $balance->total_ouverture -= $transaction->montant;
                    break;
            }

            $balance->nombre_transactions = max(0, $balance->nombre_transactions - 1);
            $balance->save();

            Log::info('Balance inversée après annulation de transaction', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'nouvelle_balance' => $balance->balance,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'inversion de la balance', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
