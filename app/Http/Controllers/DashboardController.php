<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Client;
use App\Models\AccountTransaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Vérifier la permission
        $user = Auth::user();
        if (!$user->hasPermissionTo('dashboard.view')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        }

        // Récupérer les branches pour le filtre
        $branches = Branch::all();

        // Filtre par branche
        $branchId = $request->input('branch_id');

        // Si l'utilisateur n'est pas admin et a une branche assignée, forcer le filtre
        if (!$user->isAdmin() && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        // Statistiques globales
        $stats = $this->getStatistics($branchId);

        // Données pour les graphiques
        $chartsData = $this->getChartsData($branchId);

        return view('dashboard.analytics', compact('stats', 'chartsData', 'branches', 'branchId'));
    }

    private function getStatistics($branchId = null)
    {
        // Montant total disponible dans le système (utiliser amount_after qui est le solde réel)
        $totalBalance = Account::when($branchId, function($q) use ($branchId) {
            return $q->whereHas('client', function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        })->sum('amount_after');

        // Nombre de clients
        $totalClients = Client::when($branchId, function($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })->count();

        // Nombre de comptes
        $totalAccounts = Account::when($branchId, function($q) use ($branchId) {
            return $q->whereHas('client', function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        })->count();

        // Nombre et montant total des dépôts (paiements)
        $totalPayments = AccountTransaction::where('type', 'PAIEMENT')
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->count();

        $totalPaymentsAmount = AccountTransaction::where('type', 'PAIEMENT')
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->sum('amount');

        // Nombre et montant total des retraits
        $totalWithdrawals = AccountTransaction::where('type', 'RETRAIT')
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->count();

        $totalWithdrawalsAmount = AccountTransaction::where('type', 'RETRAIT')
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->sum('amount');

        // Dépôts du jour
        $todayPayments = AccountTransaction::where('type', 'PAIEMENT')
            ->whereDate('created_at', today())
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->count();

        $todayPaymentsAmount = AccountTransaction::where('type', 'PAIEMENT')
            ->whereDate('created_at', today())
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->sum('amount');

        // Retraits du jour
        $todayWithdrawals = AccountTransaction::where('type', 'RETRAIT')
            ->whereDate('created_at', today())
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->count();

        $todayWithdrawalsAmount = AccountTransaction::where('type', 'RETRAIT')
            ->whereDate('created_at', today())
            ->when($branchId, function($q) use ($branchId) {
                $q->whereHas('account.client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->sum('amount');

        // Comptes actifs vs inactifs (utiliser 'actif' et 'inactif' selon enum)
        $activeAccounts = Account::where('status', 'actif')
            ->when($branchId, function($q) use ($branchId) {
                return $q->whereHas('client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->count();

        $inactiveAccounts = Account::where('status', 'inactif')
            ->when($branchId, function($q) use ($branchId) {
                return $q->whereHas('client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->count();

        return [
            'totalBalance' => $totalBalance,
            'totalClients' => $totalClients,
            'totalAccounts' => $totalAccounts,
            'totalPayments' => $totalPayments,
            'totalPaymentsAmount' => $totalPaymentsAmount,
            'totalWithdrawals' => $totalWithdrawals,
            'totalWithdrawalsAmount' => $totalWithdrawalsAmount,
            'todayPayments' => $todayPayments,
            'todayPaymentsAmount' => $todayPaymentsAmount,
            'todayWithdrawals' => $todayWithdrawals,
            'todayWithdrawalsAmount' => $todayWithdrawalsAmount,
            'activeAccounts' => $activeAccounts,
            'inactiveAccounts' => $inactiveAccounts,
        ];
    }

    private function getChartsData($branchId = null)
    {
        // Transactions des 7 derniers jours
        $last7Days = [];
        $paymentsLast7Days = [];
        $withdrawalsLast7Days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $last7Days[] = $date->format('d/m');

            $paymentsCount = AccountTransaction::where('type', 'PAIEMENT')
                ->whereDate('created_at', $date)
                ->when($branchId, function($q) use ($branchId) {
                    $q->whereHas('account.client', function($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    });
                })
                ->sum('amount');

            $withdrawalsCount = AccountTransaction::where('type', 'RETRAIT')
                ->whereDate('created_at', $date)
                ->when($branchId, function($q) use ($branchId) {
                    $q->whereHas('account.client', function($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    });
                })
                ->sum('amount');

            $paymentsLast7Days[] = $paymentsCount;
            $withdrawalsLast7Days[] = $withdrawalsCount;
        }

        // Distribution par branche
        $branchesData = Branch::withCount(['accounts', 'clients'])
            ->when($branchId, function($q) use ($branchId) {
                return $q->where('id', $branchId);
            })
            ->get()
            ->map(function($branch) {
                // Calculer le solde total via les comptes de la branche (utiliser amount_after)
                $accountsBalance = Account::whereHas('client', function($q) use ($branch) {
                    $q->where('branch_id', $branch->id);
                })->sum('amount_after');

                return [
                    'name' => $branch->name,
                    'clients' => $branch->clients_count,
                    'accounts' => $branch->accounts_count,
                    'balance' => $accountsBalance,
                    'cash_balance' => $branch->cash_balance, // Solde caisse physique
                ];
            });

        // Top 5 clients avec le plus de solde (utiliser amount_after)
        $topClients = Account::select('client_id', DB::raw('SUM(amount_after) as total_balance'))
            ->with('client')
            ->when($branchId, function($q) use ($branchId) {
                return $q->whereHas('client', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            })
            ->groupBy('client_id')
            ->having('total_balance', '>', 0)
            ->orderByDesc('total_balance')
            ->limit(5)
            ->get()
            ->filter(function($account) {
                return $account->client !== null;
            })
            ->map(function($account) {
                return [
                    'name' => $account->client->first_name . ' ' . $account->client->last_name,
                    'balance' => $account->total_balance,
                ];
            });

        return [
            'last7Days' => $last7Days,
            'paymentsLast7Days' => $paymentsLast7Days,
            'withdrawalsLast7Days' => $withdrawalsLast7Days,
            'branchesData' => $branchesData,
            'topClients' => $topClients,
        ];
    }

    public function getRealtimeStats(Request $request)
    {
        // Endpoint AJAX pour mise à jour en temps réel
        $branchId = $request->input('branch_id');

        $user = Auth::user();
        if (!$user->isAdmin() && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        $stats = $this->getStatistics($branchId);

        return response()->json($stats);
    }
}
