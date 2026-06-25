<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Client;
use App\Models\CurrentAccount;
use App\Models\CurrentAccountTransaction;
use App\Services\CurrentAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CurrentAccountController extends Controller
{
    public function __construct(private CurrentAccountService $service) {}

    public function index(Request $request)
    {
        $user    = Auth::user();
        $isAgent = $user->role === 'agent';
        $isAdmin = in_array($user->role, ['admin', 'comptable']);

        $query = CurrentAccount::with(['client', 'branch']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status') && $isAdmin) {
            $query->where('status', $request->status);
        }

        if ($isAgent) {
            // Agent : liste vide tant qu'il ne recherche pas
            if (!$request->filled('search')) {
                $accounts = CurrentAccount::whereRaw('1=0')->paginate(20);
            } else {
                // Un client peut faire des opérations dans n'importe quelle succursale
                $accounts = $query->latest()->paginate(20)->withQueryString();
            }
        } else {
            $accounts = $query->latest()->paginate(20)->withQueryString();
        }

        // Statistiques : admin/comptable uniquement
        $totalAccounts  = $isAdmin ? CurrentAccount::count() : 0;
        $activeAccounts = $isAdmin ? CurrentAccount::where('status', 'actif')->count() : 0;
        $totalBalance   = $isAdmin ? CurrentAccount::where('status', 'actif')->sum('balance') : 0;

        return view('current-accounts.index', compact(
            'accounts', 'totalAccounts', 'activeAccounts', 'totalBalance', 'isAgent', 'isAdmin'
        ));
    }

    public function dashboard(Request $request)
    {
        // Période sélectionnée
        $periode = $request->input('periode', '30d');
        [$dateDebut, $dateFin] = match($periode) {
            'today' => [now()->startOfDay(),   now()->endOfDay()],
            '7d'    => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfDay()],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay()], // 30d
        };

        // ── KPI en-tête ────────────────────────────────────────────────────────
        $kpi = [
            'solde_total'      => CurrentAccount::where('status', 'actif')->sum('balance'),
            'nb_actif'         => CurrentAccount::where('status', 'actif')->count(),
            'nb_suspendu'      => CurrentAccount::where('status', 'suspendu')->count(),
            'nb_cloture'       => CurrentAccount::where('status', 'cloture')->count(),
            'depot_today'      => CurrentAccountTransaction::where('type', 'DEPOT')->whereDate('created_at', today())->sum('amount'),
            'depot_today_nb'   => CurrentAccountTransaction::where('type', 'DEPOT')->whereDate('created_at', today())->count(),
            'retrait_today'    => CurrentAccountTransaction::where('type', 'RETRAIT')->whereDate('created_at', today())->sum('amount'),
            'retrait_today_nb' => CurrentAccountTransaction::where('type', 'RETRAIT')->whereDate('created_at', today())->count(),
        ];

        // ── Flux financiers sur la période ─────────────────────────────────────
        $base = CurrentAccountTransaction::whereBetween('created_at', [$dateDebut, $dateFin]);

        $flux = [
            'depots'          => (clone $base)->where('type', 'DEPOT')->sum('amount'),
            'retraits'        => (clone $base)->where('type', 'RETRAIT')->sum('amount'),
            'frais_ouverture' => (clone $base)->where('type', 'FRAIS_OUVERTURE')->sum('amount'),
            'frais_service'   => (clone $base)->where('type', 'FRAIS_SERVICE')->sum('amount'),
        ];
        $flux['net']            = $flux['depots'] - $flux['retraits'];
        $flux['revenus_frais']  = $flux['frais_ouverture'] + $flux['frais_service'];

        // ── Graphique 30 jours : dépôts vs retraits ────────────────────────────
        $raw = CurrentAccountTransaction::whereIn('type', ['DEPOT', 'RETRAIT'])
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as jour'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('jour', 'type')
            ->orderBy('jour')
            ->get()
            ->groupBy('jour');

        $chartLabels  = [];
        $chartDepots  = [];
        $chartRetraits = [];

        for ($i = 29; $i >= 0; $i--) {
            $day  = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d/m');
            $chartLabels[]   = $label;
            $chartDepots[]   = isset($raw[$day]) ? (float) $raw[$day]->firstWhere('type', 'DEPOT')?->total ?? 0 : 0;
            $chartRetraits[] = isset($raw[$day]) ? (float) $raw[$day]->firstWhere('type', 'RETRAIT')?->total ?? 0 : 0;
        }

        // ── Par succursale ─────────────────────────────────────────────────────
        $parBranche = DB::table('current_accounts')
            ->leftJoin('branches', 'current_accounts.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('COALESCE(branches.name, "Sans branche") as branche'),
                DB::raw('COUNT(CASE WHEN current_accounts.status = "actif" THEN 1 END) as nb_actif'),
                DB::raw('SUM(CASE WHEN current_accounts.status = "actif" THEN current_accounts.balance ELSE 0 END) as solde_total')
            )
            ->groupBy('current_accounts.branch_id', 'branches.name')
            ->orderByDesc('solde_total')
            ->get();

        // Dépôts et retraits ce mois par branche
        $mouvBranche = DB::table('current_account_transactions')
            ->leftJoin('current_accounts', 'current_account_transactions.current_account_id', '=', 'current_accounts.id')
            ->leftJoin('branches', 'current_accounts.branch_id', '=', 'branches.id')
            ->whereIn('current_account_transactions.type', ['DEPOT', 'RETRAIT'])
            ->where('current_account_transactions.created_at', '>=', now()->startOfMonth())
            ->select(
                DB::raw('COALESCE(branches.name, "Sans branche") as branche'),
                'current_account_transactions.type',
                DB::raw('SUM(current_account_transactions.amount) as total')
            )
            ->groupBy('current_accounts.branch_id', 'branches.name', 'current_account_transactions.type')
            ->get()
            ->groupBy('branche');

        // Enrichir $parBranche avec les mouvements
        $parBranche = $parBranche->map(function ($row) use ($mouvBranche) {
            $mv = $mouvBranche->get($row->branche, collect());
            $row->depot_mois   = (float) $mv->firstWhere('type', 'DEPOT')?->total ?? 0;
            $row->retrait_mois = (float) $mv->firstWhere('type', 'RETRAIT')?->total ?? 0;
            return $row;
        });

        // ── Alertes comptables ─────────────────────────────────────────────────
        $alertes = [
            'solde_negatif'     => CurrentAccount::where('balance', '<', 0)->count(),
            'frais_non_preleves' => CurrentAccount::where('status', 'actif')
                ->where(function ($q) {
                    $q->whereNull('last_fee_charged_at')
                      ->orWhere('last_fee_charged_at', '<', now()->startOfMonth());
                })->count(),
            'suspendus_30j'     => CurrentAccount::where('status', 'suspendu')
                ->where('updated_at', '<', now()->subDays(30))
                ->count(),
        ];

        return view('current-accounts.dashboard', compact(
            'kpi', 'flux', 'periode',
            'chartLabels', 'chartDepots', 'chartRetraits',
            'parBranche', 'alertes'
        ));
    }

    public function report(Request $request)
    {
        $request->validate([
            'date_debut' => 'nullable|date|before_or_equal:date_fin',
            'date_fin'   => 'nullable|date|after_or_equal:date_debut',
        ]);

        $dateDebut = $request->filled('date_debut')
            ? \Carbon\Carbon::parse($request->date_debut)->startOfDay()
            : now()->startOfMonth();

        $dateFin = $request->filled('date_fin')
            ? \Carbon\Carbon::parse($request->date_fin)->endOfDay()
            : now()->endOfDay();

        $generated = $request->filled('date_debut') || $request->filled('date_fin');

        // ── Flux globaux sur la période ────────────────────────────────────────
        $base = CurrentAccountTransaction::whereBetween('created_at', [$dateDebut, $dateFin]);

        $flux = [
            'depots'          => (clone $base)->where('type', 'DEPOT')->sum('amount'),
            'depots_nb'       => (clone $base)->where('type', 'DEPOT')->count(),
            'retraits'        => (clone $base)->where('type', 'RETRAIT')->sum('amount'),
            'retraits_nb'     => (clone $base)->where('type', 'RETRAIT')->count(),
            'frais_ouverture' => (clone $base)->where('type', 'FRAIS_OUVERTURE')->sum('amount'),
            'frais_ouverture_nb' => (clone $base)->where('type', 'FRAIS_OUVERTURE')->count(),
            'frais_service'   => (clone $base)->where('type', 'FRAIS_SERVICE')->sum('amount'),
            'frais_service_nb' => (clone $base)->where('type', 'FRAIS_SERVICE')->count(),
        ];
        $flux['net']           = $flux['depots'] - $flux['retraits'];
        $flux['revenus_frais'] = $flux['frais_ouverture'] + $flux['frais_service'];
        $flux['total_ops']     = $flux['depots_nb'] + $flux['retraits_nb'] + $flux['frais_ouverture_nb'] + $flux['frais_service_nb'];

        // ── Solde snapshot à la fin de la période ─────────────────────────────
        $soldeFin = CurrentAccount::where('status', 'actif')->sum('balance');

        // ── Détail journalier ──────────────────────────────────────────────────
        $joursDetail = DB::table('current_account_transactions')
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->select(
                DB::raw('DATE(created_at) as jour'),
                'type',
                DB::raw('COUNT(*) as nb'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('jour', 'type')
            ->orderBy('jour')
            ->get()
            ->groupBy('jour');

        // ── Par succursale sur la période ──────────────────────────────────────
        $parBranche = DB::table('current_accounts')
            ->leftJoin('branches', 'current_accounts.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('COALESCE(branches.name, "Sans succursale") as branche'),
                DB::raw('COUNT(CASE WHEN current_accounts.status = "actif" THEN 1 END) as nb_actif'),
                DB::raw('SUM(CASE WHEN current_accounts.status = "actif" THEN current_accounts.balance ELSE 0 END) as solde_total')
            )
            ->groupBy('current_accounts.branch_id', 'branches.name')
            ->orderByDesc('solde_total')
            ->get();

        $mouvBranche = DB::table('current_account_transactions')
            ->leftJoin('current_accounts', 'current_account_transactions.current_account_id', '=', 'current_accounts.id')
            ->leftJoin('branches', 'current_accounts.branch_id', '=', 'branches.id')
            ->whereBetween('current_account_transactions.created_at', [$dateDebut, $dateFin])
            ->select(
                DB::raw('COALESCE(branches.name, "Sans succursale") as branche'),
                'current_account_transactions.type',
                DB::raw('COUNT(*) as nb'),
                DB::raw('SUM(current_account_transactions.amount) as total')
            )
            ->groupBy('current_accounts.branch_id', 'branches.name', 'current_account_transactions.type')
            ->get()
            ->groupBy('branche');

        $parBranche = $parBranche->map(function ($row) use ($mouvBranche) {
            $mv = $mouvBranche->get($row->branche, collect());
            $row->depot_periode   = (float) $mv->firstWhere('type', 'DEPOT')?->total ?? 0;
            $row->depot_nb        = (int)   $mv->firstWhere('type', 'DEPOT')?->nb    ?? 0;
            $row->retrait_periode = (float) $mv->firstWhere('type', 'RETRAIT')?->total ?? 0;
            $row->retrait_nb      = (int)   $mv->firstWhere('type', 'RETRAIT')?->nb    ?? 0;
            return $row;
        });

        // ── Ouvertures de comptes sur la période ───────────────────────────────
        $ouvertures = CurrentAccount::whereBetween('created_at', [$dateDebut, $dateFin])->count();

        return view('current-accounts.report', compact(
            'dateDebut', 'dateFin', 'generated',
            'flux', 'soldeFin', 'joursDetail',
            'parBranche', 'ouvertures'
        ));
    }

    public function create(Request $request)
    {
        $fraisOuverture  = AppSetting::get('cc_frais_ouverture', 200);
        $client          = null;
        $searchPerformed = false;

        if ($request->filled('search')) {
            $searchPerformed = true;
            $term   = trim($request->search);
            $client = Client::where('email', $term)
                ->orWhere('phone', $term)
                ->orWhere('client_id', $term)
                ->first();
        }

        return view('current-accounts.create', compact('fraisOuverture', 'client', 'searchPerformed'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'id_nif_cin'      => 'required|string',
            'payment_method'  => 'required|in:cash,moncash,bank_transfer',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        // Vérification KYC
        if ($client->status_kyc !== 'verified') {
            return back()->withInput()
                ->with('error', 'Le KYC du client n\'est pas encore vérifié. Veuillez compléter le KYC avant d\'ouvrir un compte courant.');
        }

        // Vérification NIF/CIN
        if (empty($client->id_nif_cin)) {
            return back()->withInput()
                ->with('error', 'Aucun NIF/CIN enregistré pour ce client. Veuillez mettre à jour son dossier.');
        }

        if (strtoupper(trim($validated['id_nif_cin'])) !== strtoupper(trim($client->id_nif_cin))) {
            return back()->withInput()
                ->with('error', 'Le NIF/CIN saisi ne correspond pas au dossier du client. Opération refusée.');
        }

        // Un seul compte courant actif par client
        $hasActive = CurrentAccount::where('client_id', $client->id)->where('status', 'actif')->exists();
        if ($hasActive) {
            return back()->withInput()->with('error', 'Ce client possède déjà un compte courant actif.');
        }

        try {
            $account = $this->service->openAccount($client, $validated['payment_method']);

            return redirect()
                ->route('current-accounts.show', $account)
                ->with('success', "Compte courant ouvert avec succès ! Numéro : {$account->account_number}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de l\'ouverture : ' . $e->getMessage());
        }
    }

    public function show(CurrentAccount $currentAccount)
    {
        $currentAccount->load(['client', 'branch', 'creator']);
        $transactions = $currentAccount->transactions()->with('creator')->latest()->paginate(15);
        $canChangeStatus = in_array(Auth::user()->role, ['admin', 'comptable']);

        return view('current-accounts.show', compact('currentAccount', 'transactions', 'canChangeStatus'));
    }

    public function depositForm(CurrentAccount $currentAccount)
    {
        if (!$currentAccount->isActive()) {
            return redirect()->route('current-accounts.show', $currentAccount)
                ->with('error', 'Impossible d\'effectuer un dépôt sur un compte inactif.');
        }
        return view('current-accounts.deposit', compact('currentAccount'));
    }

    public function deposit(Request $request, CurrentAccount $currentAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,moncash,bank_transfer',
            'note'   => 'nullable|string|max:500',
        ]);

        if (!$currentAccount->isActive()) {
            return back()->with('error', 'Impossible d\'effectuer un dépôt sur un compte inactif.');
        }

        try {
            $this->service->deposit($currentAccount, $validated['amount'], $validated['method'], $validated['note'] ?? null);

            return redirect()
                ->route('current-accounts.show', $currentAccount)
                ->with('success', 'Dépôt de ' . number_format($validated['amount'], 2) . ' HTG effectué avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function withdrawForm(CurrentAccount $currentAccount)
    {
        return view('current-accounts.withdraw', compact('currentAccount'));
    }

    public function withdraw(Request $request, CurrentAccount $currentAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,moncash,bank_transfer',
            'note'   => 'nullable|string|max:500',
        ]);

        if (!$currentAccount->isActive()) {
            return back()->with('error', 'Impossible d\'effectuer un retrait sur un compte inactif.');
        }

        try {
            $this->service->withdraw($currentAccount, $validated['amount'], $validated['method'], $validated['note'] ?? null);

            return redirect()
                ->route('current-accounts.show', $currentAccount)
                ->with('success', 'Retrait de ' . number_format($validated['amount'], 2) . ' HTG effectué avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, CurrentAccount $currentAccount)
    {
        if (!in_array(Auth::user()->role, ['admin', 'comptable'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:actif,suspendu,cloture',
        ]);

        $currentAccount->update(['status' => $validated['status']]);

        return redirect()
            ->route('current-accounts.show', $currentAccount)
            ->with('success', 'Statut mis à jour : ' . $validated['status']);
    }

    public function settings()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $settings = AppSetting::getByGroup('compte_courant');

        return view('current-accounts.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'cc_frais_ouverture'       => 'required|numeric|min:0',
            'cc_frais_service_mensuel' => 'required|numeric|min:0',
            'cc_frais_service_actif'   => 'nullable|boolean',
        ]);

        AppSetting::set('cc_frais_ouverture', $validated['cc_frais_ouverture'], 'number');
        AppSetting::set('cc_frais_service_mensuel', $validated['cc_frais_service_mensuel'], 'number');
        AppSetting::set('cc_frais_service_actif', $request->boolean('cc_frais_service_actif') ? 'true' : 'false', 'boolean');

        return redirect()
            ->route('current-accounts.settings')
            ->with('success', 'Paramètres compte courant mis à jour.');
    }
}
