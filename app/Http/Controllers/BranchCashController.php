<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\AccountTransaction;
use App\Models\FundMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchCashController extends Controller
{
    public function index(Request $request)
    {
        // Vérifier la permission
        $user = Auth::user();
        if (!$user->hasPermissionTo('branch-cash.view')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        }

        // Récupérer les branches
        $branches = Branch::all();

        // Filtre par branche
        $branchId = $request->input('branch_id');

        // Si l'utilisateur n'est pas admin et a une branche assignée, forcer le filtre
        if (!$user->isAdmin() && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        // Sélectionner la première branche par défaut si aucune n'est sélectionnée
        if (!$branchId && $branches->count() > 0) {
            $branchId = $branches->first()->id;
        }

        $branch = null;
        $transactions = collect();
        $stats = [];

        if ($branchId) {
            $branch = Branch::findOrFail($branchId);

            // Historique des mouvements (derniers 30 jours)
            $transactions = $this->getBranchTransactions($branchId);

            // Statistiques
            $stats = $this->getBranchStats($branchId);
        }

        return view('branch-cash.index', compact('branches', 'branchId', 'branch', 'transactions', 'stats'));
    }

    private function getBranchTransactions($branchId)
    {
        $transactions = collect();

        // 1. Transactions clients (Dépôts/Retraits)
        $clientTransactions = AccountTransaction::whereHas('account.client', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->whereDate('created_at', '>=', now()->subDays(30))
        ->orderByDesc('created_at')
        ->get()
        ->map(function($transaction) {
            return [
                'date' => $transaction->created_at,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'reference' => $transaction->id_transaction,
                'description' => $transaction->type === 'PAIEMENT' ? 'Dépôt client' : 'Retrait client',
                'impact' => $transaction->type === 'PAIEMENT' ? 'IN' : 'OUT',
                'category' => 'CLIENT',
            ];
        });

        // 2. Mouvements de fonds (Transferts)
        $fundMovements = FundMovement::where(function($q) use ($branchId) {
            $q->where('source_branch_id', $branchId)
              ->orWhere('destination_branch_id', $branchId);
        })
        ->where('status', 'APPROVED')
        ->whereDate('created_at', '>=', now()->subDays(30))
        ->orderByDesc('created_at')
        ->get()
        ->map(function($movement) use ($branchId) {
            $isOut = $movement->source_branch_id == $branchId;
            return [
                'date' => $movement->created_at,
                'type' => $isOut ? 'TRANSFERT_OUT' : 'TRANSFERT_IN',
                'amount' => $movement->amount,
                'reference' => $movement->reference,
                'description' => $isOut
                    ? 'Décaissement vers ' . ($movement->destinationBranch->name ?? 'Central')
                    : 'Réception de ' . ($movement->sourceBranch->name ?? 'Central'),
                'impact' => $isOut ? 'OUT' : 'IN',
                'category' => 'TRANSFERT',
            ];
        });

        // Combiner et trier (concat au lieu de merge pour éviter l'erreur getKey())
        $transactions = $clientTransactions->concat($fundMovements)->sortByDesc(function($item) {
            return $item['date'];
        })->values();

        return $transactions;
    }

    private function getBranchStats($branchId)
    {
        // Solde caisse actuel
        $branch = Branch::find($branchId);
        $currentBalance = $branch->cash_balance ?? 0;

        // Transactions du jour
        $todayDeposits = AccountTransaction::whereHas('account.client', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->where('type', 'PAIEMENT')
        ->whereDate('created_at', today())
        ->sum('amount');

        $todayWithdrawals = AccountTransaction::whereHas('account.client', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->where('type', 'RETRAIT')
        ->whereDate('created_at', today())
        ->sum('amount');

        // Transferts du jour
        $todayTransfersOut = FundMovement::where('source_branch_id', $branchId)
            ->where('status', 'APPROVED')
            ->whereDate('approved_at', today())
            ->sum('amount');

        $todayTransfersIn = FundMovement::where('destination_branch_id', $branchId)
            ->where('status', 'APPROVED')
            ->whereDate('approved_at', today())
            ->sum('amount');

        // Transactions du mois
        $monthDeposits = AccountTransaction::whereHas('account.client', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->where('type', 'PAIEMENT')
        ->whereMonth('created_at', now()->month)
        ->sum('amount');

        $monthWithdrawals = AccountTransaction::whereHas('account.client', function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->where('type', 'RETRAIT')
        ->whereMonth('created_at', now()->month)
        ->sum('amount');

        return [
            'current_balance' => $currentBalance,
            'today_deposits' => $todayDeposits,
            'today_withdrawals' => $todayWithdrawals,
            'today_transfers_out' => $todayTransfersOut,
            'today_transfers_in' => $todayTransfersIn,
            'today_net' => $todayDeposits - $todayWithdrawals + $todayTransfersIn - $todayTransfersOut,
            'month_deposits' => $monthDeposits,
            'month_withdrawals' => $monthWithdrawals,
        ];
    }

    public function updateBalance(Request $request, Branch $branch)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branch-cash.manage')) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:ADD,SUBTRACT',
            'reason' => 'required|string|max:500',
        ]);

        $amount = $request->amount;
        $type = $request->type;

        if ($type === 'SUBTRACT' && $branch->cash_balance < $amount) {
            return back()->with('error', 'Solde insuffisant en caisse.');
        }

        $newBalance = $type === 'ADD'
            ? $branch->cash_balance + $amount
            : $branch->cash_balance - $amount;

        $branch->update(['cash_balance' => $newBalance]);

        // Créer un enregistrement de mouvement
        FundMovement::create([
            'reference' => FundMovement::generateReference(),
            'type' => $type === 'ADD' ? 'IN' : 'OUT',
            'amount' => $amount,
            'source_branch_id' => $type === 'SUBTRACT' ? $branch->id : null,
            'destination_branch_id' => $type === 'ADD' ? $branch->id : null,
            'source_type' => $type === 'ADD' ? 'EXTERNE' : 'SUCCURSALE',
            'external_source' => $type === 'ADD' ? 'Ajustement manuel' : null,
            'reason' => $request->reason,
            'status' => 'APPROVED',
            'created_by' => Auth::id(),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Solde caisse mis à jour avec succès.');
    }
}
