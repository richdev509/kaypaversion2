<?php

namespace App\Observers;

use App\Models\AccountTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountTransactionObserver
{
    /**
     * Handle the AccountTransaction "created" event.
     * Met à jour automatiquement le cash_balance de la branche du CLIENT
     */
    public function created(AccountTransaction $transaction): void
    {
        try {
            // Ne pas traiter les transactions annulées
            if ($transaction->status === 'CANCELLED') {
                return;
            }

            // Récupérer la branche via le CLIENT de la transaction (pas l'utilisateur connecté)
            $account = $transaction->account;
            if (!$account) {
                Log::warning("AccountTransaction {$transaction->id}: Compte introuvable");
                return;
            }

            $client = $account->client;
            if (!$client || !$client->branch_id) {
                Log::warning("AccountTransaction {$transaction->id}: Client sans branch_id");
                return;
            }

            $branch = \App\Models\Branch::find($client->branch_id);

            if (!$branch) {
                Log::warning("AccountTransaction {$transaction->id}: Branch {$client->branch_id} introuvable");
                return;
            }

            // Mettre à jour le cash_balance selon le type de transaction
            if ($transaction->type === 'PAIEMENT' || $transaction->type === 'Paiement initial') {
                // PAIEMENT ou PAIEMENT INITIAL = argent entre dans la caisse (+)
                $branch->increment('cash_balance', $transaction->amount);
                Log::info("Branch {$branch->name}: +{$transaction->amount} HTG ({$transaction->type} #{$transaction->id})");

            } elseif ($transaction->type === 'RETRAIT') {
                // RETRAIT = argent sort de la caisse (-)
                $branch->decrement('cash_balance', $transaction->amount);
                Log::info("Branch {$branch->name}: -{$transaction->amount} HTG (RETRAIT #{$transaction->id})");

                // Recharger le compte pour avoir les dernières données
                $account->refresh();

                // Vérifier si le solde du compte est à zéro après le retrait
                // Si oui, clôturer automatiquement le compte
                if ($account->amount_after <= 0 && $account->status === 'actif') {
                    $account->update(['status' => 'cloture']);

                    // Enregistrer l'action de clôture automatique (sans déclencher l'Observer)
                    DB::table('account_transactions')->insert([
                        'account_id' => $account->account_id,
                        'client_id' => $account->client_id,
                        'type' => 'STATUS_CHANGE',
                        'amount' => 0,
                        'amount_after' => 0,
                        'method' => 'system',
                        'reference' => 'AUTO-CLOTURE-' . time(),
                        'note' => "Clôture automatique: solde à zéro après retrait (Transaction #{$transaction->id})",
                        'created_by' => $transaction->created_by,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    Log::info("Account {$account->account_id}: Clôturé automatiquement (solde = 0 après retrait #{$transaction->id})");
                }

            } elseif ($transaction->type === 'AJUSTEMENT-DEPOT') {
                // AJUSTEMENT-DEPOT = correction avec augmentation
                $branch->increment('cash_balance', $transaction->amount);
                Log::info("Branch {$branch->name}: +{$transaction->amount} HTG (AJUSTEMENT-DEPOT #{$transaction->id})");

            } elseif ($transaction->type === 'AJUSTEMENT-RETRAIT') {
                // AJUSTEMENT-RETRAIT = correction avec diminution
                $branch->decrement('cash_balance', $transaction->amount);
                Log::info("Branch {$branch->name}: -{$transaction->amount} HTG (AJUSTEMENT-RETRAIT #{$transaction->id})");
            }

        } catch (\Exception $e) {
            Log::error("Erreur mise à jour cash_balance pour transaction {$transaction->id}: {$e->getMessage()}");
        }
    }
}
