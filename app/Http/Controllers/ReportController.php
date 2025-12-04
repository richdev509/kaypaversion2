<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Afficher le formulaire de génération de rapport
     */
    public function index()
    {
        $user = Auth::user();

        // Les agents ne voient que leur branche
        if ($user->isAgent()) {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        return view('reports.index', compact('branches'));
    }

    /**
     * Générer un rapport
     */
    public function generate(Request $request)
    {
        $validationRules = [
            'period_type' => 'required|in:daily,weekly,monthly,custom',
            'type' => 'required|in:deposit,withdrawal,all',
            'branch_id' => 'nullable|exists:branches,id',
        ];

        // Ajouter la validation des dates uniquement si période personnalisée
        if ($request->period_type === 'custom') {
            $validationRules['start_date'] = 'required|date';
            $validationRules['end_date'] = 'required|date|after_or_equal:start_date';
        }

        $request->validate($validationRules);

        $user = Auth::user();

        // Vérification des permissions par branche
        if ($user->isAgent() && $request->branch_id != $user->branch_id) {
            abort(403, 'Vous ne pouvez générer que des rapports pour votre branche');
        }

        // Calculer les dates selon le type de période
        [$startDate, $endDate] = $this->calculateDateRange($request->period_type, $request->start_date, $request->end_date);

        // Générer les données du rapport
        $reportData = $this->generateReportData(
            $request->type,
            $startDate,
            $endDate,
            $request->branch_id
        );

        // Pagination des transactions détaillées
        $transactions = $this->getTransactionsPaginated(
            $request->type,
            $startDate,
            $endDate,
            $request->branch_id
        );

        $branches = Branch::orderBy('name')->get();
        $selectedBranch = $request->branch_id ? Branch::find($request->branch_id) : null;

        return view('reports.show', compact('reportData', 'branches', 'selectedBranch', 'startDate', 'endDate', 'transactions'));
    }

    /**
     * Calculer la plage de dates selon le type de période
     */
    private function calculateDateRange($periodType, $customStart = null, $customEnd = null)
    {
        $today = Carbon::today();

        return match($periodType) {
            'daily' => [$today, $today],
            'weekly' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'monthly' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'custom' => [Carbon::parse($customStart), Carbon::parse($customEnd)],
            default => [$today, $today],
        };
    }

    /**
     * Générer les données du rapport
     */
    private function generateReportData($type, $startDate, $endDate, $branchId = null)
    {
        $data = [
            'deposits' => null,
            'withdrawals' => null,
            'summary' => [],
            'details' => [],
        ];

        // Base query: filtrer par branche via created_by et exclure les transactions annulées
        $baseQuery = AccountTransaction::whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->where('status', '!=', 'CANCELLED');

        if ($branchId) {
            $baseQuery->whereHas('creator', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        // Rapport sur les dépôts (incluant ajustements positifs)
        if (in_array($type, ['deposit', 'all'])) {
            $depositsQuery = clone $baseQuery;
            $depositsQuery->whereIn('type', ['PAIEMENT', 'AJUSTEMENT-DEPOT']);

            $depositsByDate = clone $depositsQuery;
            $recentDeposits = clone $depositsQuery;

            $data['deposits'] = [
                'total_count' => $depositsQuery->count(),
                'total_amount' => $depositsQuery->sum('amount'),
                'by_date' => $depositsByDate->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get(),
                'by_branch' => $this->getDepositsByBranch($startDate, $endDate, $branchId),
                'recent' => $recentDeposits->with(['account', 'client', 'creator'])
                    ->latest()
                    ->limit(10)
                    ->get(),
            ];
        }

        // Rapport sur les retraits (incluant ajustements négatifs)
        if (in_array($type, ['withdrawal', 'all'])) {
            $withdrawalsQuery = clone $baseQuery;
            $withdrawalsQuery->whereIn('type', ['RETRAIT', 'AJUSTEMENT-RETRAIT']);

            $withdrawalsByDate = clone $withdrawalsQuery;
            $recentWithdrawals = clone $withdrawalsQuery;

            $data['withdrawals'] = [
                'total_count' => $withdrawalsQuery->count(),
                'total_amount' => abs($withdrawalsQuery->sum('amount')),
                'by_date' => $withdrawalsByDate->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get(),
                'by_branch' => $this->getWithdrawalsByBranch($startDate, $endDate, $branchId),
                'recent' => $recentWithdrawals->with(['account', 'client', 'creator'])
                    ->latest()
                    ->limit(10)
                    ->get(),
            ];
        }

        // Résumé global
        $data['summary'] = [
            'total_deposits' => $data['deposits']['total_amount'] ?? 0,
            'total_withdrawals' => $data['withdrawals']['total_amount'] ?? 0,
            'net_balance' => ($data['deposits']['total_amount'] ?? 0) - ($data['withdrawals']['total_amount'] ?? 0),
            'deposits_count' => $data['deposits']['total_count'] ?? 0,
            'withdrawals_count' => $data['withdrawals']['total_count'] ?? 0,
        ];

        return $data;
    }

    /**
     * Obtenir les dépôts par branche
     */
    private function getDepositsByBranch($startDate, $endDate, $branchId = null)
    {
        $query = AccountTransaction::whereBetween('account_transactions.created_at', [$startDate, $endDate->endOfDay()])
            ->whereIn('type', ['PAIEMENT', 'AJUSTEMENT-DEPOT'])
            ->where('account_transactions.status', '!=', 'CANCELLED')
            ->join('users', 'account_transactions.created_by', '=', 'users.id')
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->selectRaw('branches.id, branches.name, COUNT(*) as count, SUM(account_transactions.amount) as total')
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('total');

        if ($branchId) {
            $query->where('branches.id', $branchId);
        }

        return $query->get();
    }

    /**
     * Obtenir les retraits par branche
     */
    private function getWithdrawalsByBranch($startDate, $endDate, $branchId = null)
    {
        $query = AccountTransaction::whereBetween('account_transactions.created_at', [$startDate, $endDate->endOfDay()])
            ->whereIn('type', ['RETRAIT', 'AJUSTEMENT-RETRAIT'])
            ->where('account_transactions.status', '!=', 'CANCELLED')
            ->join('users', 'account_transactions.created_by', '=', 'users.id')
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->selectRaw('branches.id, branches.name, COUNT(*) as count, SUM(account_transactions.amount) as total')
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('total');

        if ($branchId) {
            $query->where('branches.id', $branchId);
        }

        return $query->get();
    }

    /**
     * Obtenir les transactions paginées pour les détails
     */
    private function getTransactionsPaginated($type, $startDate, $endDate, $branchId = null)
    {
        $query = AccountTransaction::whereBetween('created_at', [$startDate, $endDate->endOfDay()])
            ->with(['account', 'client', 'creator.branch']);

        // Filtrer par type (incluant ajustements) et exclure les annulées
        $query->where('status', '!=', 'CANCELLED');

        if ($type === 'deposit') {
            $query->whereIn('type', ['PAIEMENT', 'AJUSTEMENT-DEPOT']);
        } elseif ($type === 'withdrawal') {
            $query->whereIn('type', ['RETRAIT', 'AJUSTEMENT-RETRAIT']);
        }

        // Filtrer par branche via created_by
        if ($branchId) {
            $query->whereHas('creator', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        return $query->latest()->paginate(50);
    }
}
