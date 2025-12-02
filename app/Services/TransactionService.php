<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Enregistrer un dépôt et gérer la dette
     */
    public function deposit(Account $account, float $amount): Account
    {
        return DB::transaction(function () use ($account, $amount) {
            // Réduire la dette de retrait si elle existe
            if ($account->withdraw > 0) {
                if ($amount <= $account->withdraw) {
                    // Dépôt partiel → réduction de dette
                    $account->withdraw -= $amount;
                    if ($account->withdraw == 0) {
                        $account->retrait_status = 0;
                    }
                } else {
                    // Dépôt supérieur → dette totalement remboursée
                    $account->withdraw = 0;
                    $account->retrait_status = 0;
                }
            }

            // Plus de contrainte de pourcentage - montant_dispo_retrait n'est plus utilisé
            // Le retrait est désormais libre basé uniquement sur le solde

            $account->save();
            return $account;
        });
    }

    /**
     * Enregistrer un retrait et créer une dette
     */
    public function withdraw(Account $account, float $amount): array
    {
        return DB::transaction(function () use ($account, $amount) {
            // Vérifier que le montant ne dépasse pas le solde
            if ($amount > $account->amount_after) {
                return ['success' => false, 'message' => 'Montant supérieur au solde disponible'];
            }

            // Créer une dette (retrait libre)
            $account->withdraw += $amount;
            $account->retrait_status = 1;

            $account->save();

            return ['success' => true];
        });
    }
}
