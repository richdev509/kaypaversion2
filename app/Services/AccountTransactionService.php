<?php

namespace App\Services;

use App\Models\AccountTransaction;

class AccountTransactionService
{
    /**
     * Calcule le nouveau solde après une transaction
     *
     * @param  string  $accountId  Identifiant métier du compte
     * @param  float   $montant    Montant de l'opération
     * @param  string  $type       'deposit' ou 'withdraw'
     * @return float               Nouveau solde
     */
    public function handleTransaction(string $accountId, float $montant, string $type): float
    {
        if (!in_array($type, ['deposit', 'withdraw'])) {
            throw new \InvalidArgumentException("Type invalide : $type");
        }

        // Récupérer la dernière transaction
        $lastTx = AccountTransaction::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        // Solde actuel
        $amountAfter = $lastTx?->amount_after ?? 0;

        // Appliquer l'opération
        if ($type === 'deposit') {
            $amountAfter += $montant;
        } elseif ($type === 'withdraw') {
            $amountAfter -= $montant;
        }

        return $amountAfter;
    }
}
