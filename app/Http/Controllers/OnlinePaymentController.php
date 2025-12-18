<?php

namespace App\Http\Controllers;

use App\Models\BalancePaiementOnline;
use App\Models\TransactionOnline;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OnlinePaymentController extends Controller
{
    /**
     * Afficher le dashboard des paiements en ligne
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->user()->hasAnyRole(['admin', 'comptable'])) {
            abort(403, 'Accès non autorisé');
        }

        // Obtenir le solde global
        $balance = BalancePaiementOnline::getSolde();

        // Paramètres de filtrage
        $dateDebut = $request->get('date_debut', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $type = $request->get('type');
        $statut = $request->get('statut');
        $search = $request->get('search');

        // Construire la requête
        $query = TransactionOnline::with('account.client')
            ->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        // Filtres
        if ($type) {
            $query->where('type', $type);
        }

        if ($statut) {
            $query->where('statut', $statut);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('account_id', 'like', "%{$search}%")
                  ->orWhere('ordre_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Obtenir les transactions paginées
        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Statistiques de la période
        $stats = $this->getStatsForPeriod($dateDebut, $dateFin);

        // Statistiques par jour pour le graphique
        $statsParJour = $this->getStatsByDay($dateDebut, $dateFin);

        // Statistiques par type
        $statsParType = $this->getStatsByType($dateDebut, $dateFin);

        // Statistiques par statut
        $statsParStatut = $this->getStatsByStatus($dateDebut, $dateFin);

        return view('online-payments.index', compact(
            'balance',
            'transactions',
            'stats',
            'statsParJour',
            'statsParType',
            'statsParStatut',
            'dateDebut',
            'dateFin',
            'type',
            'statut',
            'search'
        ));
    }

    /**
     * Afficher les détails d'une transaction
     */
    public function show($id)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'comptable'])) {
            abort(403, 'Accès non autorisé');
        }

        $transaction = TransactionOnline::with('account.client')->findOrFail($id);

        return view('online-payments.show', compact('transaction'));
    }

    /**
     * Obtenir les statistiques pour une période
     */
    private function getStatsForPeriod($dateDebut, $dateFin)
    {
        $debut = Carbon::parse($dateDebut)->startOfDay();
        $fin = Carbon::parse($dateFin)->endOfDay();

        return [
            // Totaux généraux
            'total_transactions' => TransactionOnline::whereBetween('created_at', [$debut, $fin])->count(),
            'total_montant' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('statut', 'reussie')->sum('montant'),

            // Par type
            'depot_count' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('type', 'depot')->where('statut', 'reussie')->count(),
            'depot_montant' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('type', 'depot')->where('statut', 'reussie')->sum('montant'),

            'retrait_count' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('type', 'retrait')->where('statut', 'reussie')->count(),
            'retrait_montant' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('type', 'retrait')->where('statut', 'reussie')->sum('montant'),

            'ouverture_count' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('type', 'ouverture')->where('statut', 'reussie')->count(),
            'ouverture_montant' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('type', 'ouverture')->where('statut', 'reussie')->sum('montant'),

            // Par statut
            'reussie_count' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('statut', 'reussie')->count(),
            'en_cours_count' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('statut', 'en_cours')->count(),
            'echouee_count' => TransactionOnline::whereBetween('created_at', [$debut, $fin])
                ->where('statut', 'echouee')->count(),

            // Taux de réussite
            'taux_reussite' => $this->calculateSuccessRate($debut, $fin),
        ];
    }

    /**
     * Obtenir les statistiques par jour
     */
    private function getStatsByDay($dateDebut, $dateFin)
    {
        $debut = Carbon::parse($dateDebut)->startOfDay();
        $fin = Carbon::parse($dateFin)->endOfDay();

        return TransactionOnline::whereBetween('created_at', [$debut, $fin])
            ->where('statut', 'reussie')
            ->selectRaw('
                DATE(created_at) as date,
                type,
                COUNT(*) as nombre,
                SUM(montant) as montant_total
            ')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();
    }

    /**
     * Obtenir les statistiques par type
     */
    private function getStatsByType($dateDebut, $dateFin)
    {
        $debut = Carbon::parse($dateDebut)->startOfDay();
        $fin = Carbon::parse($dateFin)->endOfDay();

        return TransactionOnline::whereBetween('created_at', [$debut, $fin])
            ->selectRaw('
                type,
                statut,
                COUNT(*) as nombre,
                SUM(montant) as montant_total,
                AVG(montant) as montant_moyen
            ')
            ->groupBy('type', 'statut')
            ->get();
    }

    /**
     * Obtenir les statistiques par statut
     */
    private function getStatsByStatus($dateDebut, $dateFin)
    {
        $debut = Carbon::parse($dateDebut)->startOfDay();
        $fin = Carbon::parse($dateFin)->endOfDay();

        return TransactionOnline::whereBetween('created_at', [$debut, $fin])
            ->selectRaw('
                statut,
                COUNT(*) as nombre,
                SUM(montant) as montant_total
            ')
            ->groupBy('statut')
            ->get();
    }

    /**
     * Calculer le taux de réussite
     */
    private function calculateSuccessRate($debut, $fin)
    {
        $total = TransactionOnline::whereBetween('created_at', [$debut, $fin])->count();

        if ($total == 0) {
            return 0;
        }

        $reussies = TransactionOnline::whereBetween('created_at', [$debut, $fin])
            ->where('statut', 'reussie')->count();

        return round(($reussies / $total) * 100, 2);
    }

    /**
     * Exporter les transactions en CSV
     */
    public function export(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'comptable'])) {
            abort(403, 'Accès non autorisé');
        }

        $dateDebut = $request->get('date_debut', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        $transactions = TransactionOnline::with('account.client')
            ->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "transactions_online_{$dateDebut}_{$dateFin}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // En-têtes
            fputcsv($file, [
                'ID',
                'Date',
                'Compte',
                'Client',
                'Type',
                'Montant',
                'Balance Avant',
                'Balance Après',
                'Ordre ID',
                'Gateway',
                'Statut',
                'Description'
            ]);

            // Données
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->account_id,
                    $transaction->account->client->first_name ?? '' . ' ' . $transaction->account->client->last_name ?? '',
                    $transaction->type_libelle,
                    $transaction->montant,
                    $transaction->balance_avant,
                    $transaction->balance_apres,
                    $transaction->ordre_id,
                    $transaction->gateway,
                    $transaction->statut_libelle,
                    $transaction->description,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtenir les statistiques pour le dashboard (API)
     */
    public function apiStats()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'comptable'])) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $balance = BalancePaiementOnline::getSolde();
        $stats = TransactionOnline::getGlobalStats();

        return response()->json([
            'balance' => $balance,
            'stats' => $stats,
        ]);
    }
}
