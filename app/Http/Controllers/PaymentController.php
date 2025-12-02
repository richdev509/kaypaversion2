<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\AccountTransactionService;
use App\Services\TransactionService;

class PaymentController extends Controller
{
    /**
     * Store a newly created payment
     */
    public function store(Request $request, Account $account)
    {
        $request->validate([
            'method' => 'required|in:cash,moncash,bank_transfer',
            'reference' => 'nullable|string|max:100',
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => 'nullable|string|max:500',
        ]);

        if ($account->status !== 'actif') {
            return back()->with('error', "Dépôt impossible : compte {$account->status}");
        }

        $montant = $request->amount;
        $montantJournalier = $account->montant_journalier;
        $soldeTotal_Prevue = $account->plan->duree * $montantJournalier;
        $soldeActuel = $account->solde_virtuel;

        // Ne pas dépasser le total prévu
        if (($soldeActuel + $montant) > $soldeTotal_Prevue) {
            $maxPaiement = $soldeTotal_Prevue - $soldeActuel;
            return back()->with('error', "Paiement maximum autorisé: " . number_format($maxPaiement, 2) . " HTG");
        }

        $nombreJours = round($montant / $montantJournalier, 2);

        DB::beginTransaction();

        try {
            // Calculer le nouveau solde
            $service = new AccountTransactionService();
            $amountAfter = $service->handleTransaction($account->account_id, $montant, 'deposit');
            $account->update(['amount_after' => $amountAfter]);

            // Gérer la dette de retrait
            $depotService = new TransactionService();
            $depotService->deposit($account, $montant);

            // Créer l'entrée dans account_transactions (source unique)
            AccountTransaction::create([
                'account_id' => $account->account_id,
                'client_id' => $account->client_id,
                'type' => AccountTransaction::TYPE_PAIEMENT,
                'amount' => $montant,
                'amount_after' => $amountAfter,
                'method' => $request->method,
                'reference' => $request->reference,
                'created_by' => Auth::id(),
                'note' => $request->note ?? "Dépôt de " . number_format($montant, 2) . " HTG (≈ {$nombreJours} jour(s))"
            ]);

            DB::commit();

            return redirect()->route('accounts.show', $account)
                ->with('success', '✅ Dépôt de ' . number_format($montant, 2) . ' HTG enregistré avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du dépôt: ' . $e->getMessage());
        }
    }
}
