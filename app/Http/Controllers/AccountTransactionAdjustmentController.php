<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\Account;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountTransactionAdjustmentController extends Controller
{
    /**
     * Annuler une transaction existante
     */
    public function cancel(Request $request, AccountTransaction $transaction)
    {
        // Vérifier la permission (admin ou manager ou comptable)
        $user = Auth::user();
        if (!$user->hasPermissionTo('accounts.edit') && !$user->hasRole(['admin', 'manager', 'comptable'])) {
            return back()->with('error', 'Vous n\'avez pas la permission d\'annuler des transactions.');
        }

        // Vérifier que la transaction n'est pas déjà annulée
        if ($transaction->status === 'CANCELLED') {
            return back()->with('error', 'Cette transaction est déjà annulée.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:500',
        ]);

        DB::beginTransaction();

        try {
            Log::info("Début annulation transaction #{$transaction->id}");

            // Inverser l'impact sur le cash_balance de la branche DU CLIENT
            $account = $transaction->account;
            $client = $account ? $account->client : null;

            if ($client && $client->branch_id) {
                $branch = Branch::find($client->branch_id);

                if ($transaction->type === 'PAIEMENT') {
                    // Annuler un PAIEMENT = retirer l'argent qui avait été ajouté
                    $branch->decrement('cash_balance', $transaction->amount);
                    Log::info("Annulation PAIEMENT #{$transaction->id}: -{$transaction->amount} HTG de {$branch->name}");

                } elseif ($transaction->type === 'RETRAIT') {
                    // Annuler un RETRAIT = remettre l'argent qui avait été retiré
                    $branch->increment('cash_balance', $transaction->amount);
                    Log::info("Annulation RETRAIT #{$transaction->id}: +{$transaction->amount} HTG à {$branch->name}");
                }
            }

            // Inverser l'impact sur le solde du compte
            if ($account) {
                if ($transaction->type === 'PAIEMENT') {
                    // Annuler un dépôt = diminuer le solde
                    $account->decrement('amount_after', $transaction->amount);
                } elseif ($transaction->type === 'RETRAIT') {
                    // Annuler un retrait = augmenter le solde
                    $account->increment('amount_after', $transaction->amount);
                }
            }

            // Marquer la transaction comme annulée
            $transaction->update([
                'status' => 'CANCELLED',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            DB::commit();

            Log::info("Annulation transaction #{$transaction->id} terminée avec succès");

            return back()->with('success',
                'Transaction annulée avec succès. Les soldes ont été inversés automatiquement. Transaction ID: ' . $transaction->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur annulation transaction #{$transaction->id}: {$e->getMessage()}");
            Log::error("Stack trace: " . $e->getTraceAsString());
            return back()->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }

    /**
     * Créer une transaction d'ajustement (correction manuelle)
     */
    public function createAdjustment(Request $request, Account $account)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('accounts.edit') && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Vous n\'avez pas la permission de créer des ajustements.');
        }

        $request->validate([
            'adjustment_type' => 'required|in:increase,decrease',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|min:10|max:500',
        ]);

        $amount = $request->amount;
        $adjustmentType = $request->adjustment_type;

        // Vérifier que le compte a suffisamment de fonds si c'est une diminution
        if ($adjustmentType === 'decrease' && $account->amount_after < $amount) {
            return back()->with('error',
                'Solde insuffisant. Solde actuel: ' . number_format($account->amount_after, 2) . ' HTG');
        }

        DB::beginTransaction();

        try {
            // Calculer le nouveau solde
            $newBalance = $adjustmentType === 'increase'
                ? $account->amount_after + $amount
                : $account->amount_after - $amount;

            // Mettre à jour le compte
            $account->update(['amount_after' => $newBalance]);

            Log::info("Ajustement créé - Type: {$adjustmentType}, Montant: {$amount}, Nouveau solde: {$newBalance}");

            // Créer la transaction d'ajustement
            // Créer la transaction d'ajustement avec type spécifique
            // NOTE: Le cash_balance sera mis à jour automatiquement par AccountTransactionObserver
            $transactionType = $adjustmentType === 'increase' ? 'AJUSTEMENT-DEPOT' : 'AJUSTEMENT-RETRAIT';

            $adjustmentTransaction = AccountTransaction::create([
                'account_id' => $account->account_id,
                'client_id' => $account->client_id,
                'type' => $transactionType,
                'amount' => $amount,
                'amount_after' => $newBalance,
                'method' => 'Ajustement manuel',
                'reference' => 'ADJ-' . now()->format('YmdHis'),
                'note' => $request->reason,
                'created_by' => Auth::id(),
                'status' => 'ACTIVE',
            ]);

            Log::info("Transaction ajustement #{$adjustmentTransaction->id} créée - Observer va mettre à jour la succursale");

            DB::commit();

            Log::info("Ajustement #{$adjustmentTransaction->id} validé avec succès");

            return back()->with('success',
                'Ajustement créé avec succès. Nouveau solde: ' . number_format($newBalance, 2) . ' HTG. Transaction ID: ' . $adjustmentTransaction->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur création ajustement pour compte {$account->account_id}: {$e->getMessage()}");
            Log::error("Stack trace: " . $e->getTraceAsString());
            return back()->with('error', 'Erreur lors de la création de l\'ajustement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de correction depuis la page du compte
     */
    public function showCorrectionForm(Account $account)
    {
        // Récupérer les 2 dernières transactions actives
        $recentTransactions = AccountTransaction::where('account_id', $account->account_id)
            ->where('status', 'ACTIVE')
            ->whereIn('type', ['PAIEMENT', 'RETRAIT'])
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        return view('accounts.correction', compact('account', 'recentTransactions'));
    }
}
