<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\AccountTransactionService;

class WithdrawalController extends Controller
{
    /**
     * Show the form for creating a new withdrawal
     */
    public function create(Account $account)
    {
        // Vérifier que le compte est actif
        if ($account->status !== 'actif') {
            return redirect()->route('accounts.show', $account)
                ->with('error', "Retrait impossible : compte {$account->status}");
        }

        // Vérifier qu'il n'y a pas de dette active
        if ($account->hasDebt()) {
            return redirect()->route('accounts.show', $account)
                ->with('error', "Retrait impossible : une dette de " . number_format($account->withdraw, 2) . " HTG doit d'abord être remboursée.");
        }

        // Vérifier qu'il y a un solde disponible
        if ($account->getAvailableForWithdrawal() <= 0) {
            return redirect()->route('accounts.show', $account)
                ->with('error', "Retrait impossible : aucun solde disponible.");
        }

        return view('withdrawals.create', compact('account'));
    }

    /**
     * Store a newly created withdrawal
     */
    public function store(Request $request, Account $account)
    {
        $request->validate([
            'method' => 'required|in:cash,moncash,bank_transfer',
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => 'nullable|string|max:500',
        ]);

        if ($account->status !== 'actif') {
            return back()->with('error', "Retrait impossible : compte {$account->status}");
        }

        // Vérifier qu'il n'y a pas de dette active
        if ($account->hasDebt()) {
            return back()->with('error', "Retrait impossible : une dette de " . number_format($account->withdraw, 2) . " HTG doit d'abord être remboursée.");
        }

        $montant = $request->amount;
        $soldeActuel = $account->solde_virtuel;

        // Vérifier que le montant ne dépasse pas le solde
        if ($montant > $soldeActuel) {
            return back()->with('error', "Montant supérieur au solde disponible : " . number_format($soldeActuel, 2) . " HTG");
        }

        DB::beginTransaction();

        try {
            Log::info("=== DÉBUT RETRAIT ===", [
                'account_id' => $account->account_id,
                'montant' => $montant,
                'solde_actuel' => $soldeActuel,
                'method' => $request->method
            ]);

            // Déterminer le type de retrait
            $isTotal = ($montant == $soldeActuel);
            $mode = $isTotal ? 'total' : 'partiel';

            Log::info("Type de retrait: {$mode}");

            // Calculer le nouveau solde (simple déduction)
            $service = new AccountTransactionService();
            $amountAfter = $service->handleTransaction($account->account_id, $montant, 'withdraw');

            Log::info("Nouveau solde calculé: {$amountAfter}");

            // Mettre à jour le compte
            $account->update(['amount_after' => $amountAfter]);

            Log::info("Compte mis à jour");

            // Créer l'entrée dans account_transactions (source unique pour tous les retraits)
            $transaction = AccountTransaction::create([
                'account_id' => $account->account_id,
                'client_id' => $account->client_id,
                'type' => AccountTransaction::TYPE_RETRAIT,
                'amount' => $montant, // Montant positif
                'amount_after' => $amountAfter,
                'method' => $request->method,
                'mode' => $mode,
                'created_by' => Auth::id(),
                'note' => $request->note ?? "Retrait {$mode} de " . number_format($montant, 2) . " HTG"
            ]);

            Log::info("Transaction créée", ['transaction_id' => $transaction->id]);

            // Clôturer le compte si retrait total ou solde à zéro
            if ($isTotal || $amountAfter <= 0) {
                $account->update(['status' => 'cloture']);
            }

            DB::commit();

            // Message de succès
            if ($isTotal || $amountAfter <= 0) {
                $message = "✅ Retrait total de " . number_format($montant, 2) . " HTG effectué. Compte clôturé automatiquement.";
            } else {
                $message = "✅ Retrait de " . number_format($montant, 2) . " HTG effectué avec succès! Nouveau solde : " . number_format($amountAfter, 2) . " HTG";
            }

            return redirect()->route('accounts.show', $account)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du retrait: ' . $e->getMessage());
        }
    }
}
