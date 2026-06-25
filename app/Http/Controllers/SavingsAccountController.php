<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Client;
use App\Models\SavingsAccount;
use App\Models\SavingsAccountTransaction;
use App\Services\SavingsAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavingsAccountController extends Controller
{
    public function __construct(private SavingsAccountService $service) {}

    public function index(Request $request)
    {
        $user    = Auth::user();
        $isAgent = $user->role === 'agent';
        $isAdmin = in_array($user->role, ['admin', 'comptable']);

        $query = SavingsAccount::with(['client', 'branch']);

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
            if (!$request->filled('search')) {
                $accounts = SavingsAccount::whereRaw('1=0')->paginate(20);
            } else {
                // Un client peut faire des opérations dans n'importe quelle succursale
                $accounts = $query->latest()->paginate(20)->withQueryString();
            }
        } else {
            $accounts = $query->latest()->paginate(20)->withQueryString();
        }

        $totalAccounts  = $isAdmin ? SavingsAccount::count() : 0;
        $activeAccounts = $isAdmin ? SavingsAccount::where('status', 'actif')->count() : 0;
        $totalBalance   = $isAdmin ? SavingsAccount::where('status', 'actif')->sum('balance') : 0;

        return view('savings-accounts.index', compact(
            'accounts', 'totalAccounts', 'activeAccounts', 'totalBalance', 'isAgent', 'isAdmin'
        ));
    }

    public function create(Request $request)
    {
        $fraisOuverture  = AppSetting::get('sce_frais_ouverture', 0);
        $soldeMinimum    = (float) AppSetting::get('sce_solde_minimum', 500);
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

        return view('savings-accounts.create', compact('fraisOuverture', 'soldeMinimum', 'client', 'searchPerformed'));
    }

    public function store(Request $request)
    {
        $soldeMinimum = (float) \App\Models\AppSetting::get('sce_solde_minimum', 500);

        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'id_nif_cin'      => 'required|string',
            'initial_deposit' => "required|numeric|min:{$soldeMinimum}",
            'payment_method'  => 'required|in:cash,moncash,bank_transfer',
        ], [
            'initial_deposit.min' => "Le dépôt initial doit être d'au moins {$soldeMinimum} GDS.",
        ]);

        $client = Client::findOrFail($validated['client_id']);

        if ($client->status_kyc !== 'verified') {
            return back()->withInput()
                ->with('error', "Le KYC du client n'est pas encore vérifié.");
        }

        if (empty($client->id_nif_cin)) {
            return back()->withInput()
                ->with('error', "Aucun NIF/CIN enregistré pour ce client.");
        }

        if (strtoupper(trim($validated['id_nif_cin'])) !== strtoupper(trim($client->id_nif_cin))) {
            return back()->withInput()
                ->with('error', 'Le NIF/CIN saisi ne correspond pas au dossier du client.');
        }

        $hasActive = SavingsAccount::where('client_id', $client->id)->where('status', 'actif')->exists();
        if ($hasActive) {
            return back()->withInput()->with('error', 'Ce client possède déjà un compte épargne actif.');
        }

        try {
            $account = $this->service->openAccount($client, $validated['payment_method'], (float) $validated['initial_deposit']);

            return redirect()
                ->route('savings-accounts.show', $account)
                ->with('success', "Compte épargne ouvert avec succès ! Numéro : {$account->account_number}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', "Erreur lors de l'ouverture : " . $e->getMessage());
        }
    }

    public function show(SavingsAccount $savingsAccount)
    {
        $savingsAccount->load(['client', 'branch', 'creator']);
        $transactions    = $savingsAccount->transactions()->with('creator')->latest()->paginate(15);
        $canChangeStatus = in_array(Auth::user()->role, ['admin', 'comptable']);
        $tauxMensuel     = AppSetting::get('sce_taux_interet_mensuel', 0.5);

        return view('savings-accounts.show', compact(
            'savingsAccount', 'transactions', 'canChangeStatus', 'tauxMensuel'
        ));
    }

    public function depositForm(SavingsAccount $savingsAccount)
    {
        if (!$savingsAccount->isActive()) {
            return redirect()->route('savings-accounts.show', $savingsAccount)
                ->with('error', 'Impossible d\'effectuer un dépôt sur un compte inactif.');
        }
        return view('savings-accounts.deposit', compact('savingsAccount'));
    }

    public function deposit(Request $request, SavingsAccount $savingsAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,moncash,bank_transfer',
            'note'   => 'nullable|string|max:500',
        ]);

        if (!$savingsAccount->isActive()) {
            return back()->with('error', 'Impossible d\'effectuer un dépôt sur un compte inactif.');
        }

        try {
            $this->service->deposit($savingsAccount, $validated['amount'], $validated['method'], $validated['note'] ?? null);

            return redirect()
                ->route('savings-accounts.show', $savingsAccount)
                ->with('success', 'Dépôt de ' . number_format($validated['amount'], 2) . ' HTG effectué avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function withdrawForm(SavingsAccount $savingsAccount)
    {
        return view('savings-accounts.withdraw', compact('savingsAccount'));
    }

    public function withdraw(Request $request, SavingsAccount $savingsAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,moncash,bank_transfer',
            'note'   => 'nullable|string|max:500',
        ]);

        if (!$savingsAccount->isActive()) {
            return back()->with('error', 'Impossible d\'effectuer un retrait sur un compte inactif.');
        }

        try {
            $this->service->withdraw($savingsAccount, $validated['amount'], $validated['method'], $validated['note'] ?? null);

            return redirect()
                ->route('savings-accounts.show', $savingsAccount)
                ->with('success', 'Retrait de ' . number_format($validated['amount'], 2) . ' HTG effectué avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, SavingsAccount $savingsAccount)
    {
        if (!in_array(Auth::user()->role, ['admin', 'comptable'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:actif,suspendu,cloture',
        ]);

        $savingsAccount->update(['status' => $validated['status']]);

        return redirect()
            ->route('savings-accounts.show', $savingsAccount)
            ->with('success', 'Statut mis à jour : ' . $validated['status']);
    }

    public function dashboard(Request $request)
    {
        $periode = $request->input('periode', '30d');
        [$dateDebut, $dateFin] = match($periode) {
            'today' => [now()->startOfDay(),            now()->endOfDay()],
            '7d'    => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(),           now()->endOfDay()],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay()], // 30d
        };

        $soldeMinimum = (float) AppSetting::get('sce_solde_minimum', 500);
        $tauxMensuel  = (float) AppSetting::get('sce_taux_interet_mensuel', 0.5);

        // ── KPI ────────────────────────────────────────────────────────────────
        $kpi = [
            'solde_total'       => SavingsAccount::where('status', 'actif')->sum('balance'),
            'nb_actif'          => SavingsAccount::where('status', 'actif')->count(),
            'nb_suspendu'       => SavingsAccount::where('status', 'suspendu')->count(),
            'nb_cloture'        => SavingsAccount::where('status', 'cloture')->count(),
            'depot_today'       => SavingsAccountTransaction::where('type', 'DEPOT')->whereDate('created_at', today())->sum('amount'),
            'depot_today_nb'    => SavingsAccountTransaction::where('type', 'DEPOT')->whereDate('created_at', today())->count(),
            'retrait_today'     => SavingsAccountTransaction::where('type', 'RETRAIT')->whereDate('created_at', today())->sum('amount'),
            'retrait_today_nb'  => SavingsAccountTransaction::where('type', 'RETRAIT')->whereDate('created_at', today())->count(),
        ];

        // ── Flux sur la période ────────────────────────────────────────────────
        $base = SavingsAccountTransaction::whereBetween('created_at', [$dateDebut, $dateFin]);

        $flux = [
            'depots'          => (clone $base)->where('type', 'DEPOT')->sum('amount'),
            'retraits'        => (clone $base)->where('type', 'RETRAIT')->sum('amount'),
            'frais_ouverture' => (clone $base)->where('type', 'FRAIS_OUVERTURE')->sum('amount'),
            'interets'        => (clone $base)->where('type', 'INTERET')->sum('amount'),
        ];
        $flux['net'] = $flux['depots'] - $flux['retraits'];

        // ── Graphique 30 jours ────────────────────────────────────────────────
        $raw = SavingsAccountTransaction::whereIn('type', ['DEPOT', 'RETRAIT'])
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->select(DB::raw('DATE(created_at) as jour'), 'type', DB::raw('SUM(amount) as total'))
            ->groupBy('jour', 'type')
            ->orderBy('jour')
            ->get()
            ->groupBy('jour');

        $chartLabels   = [];
        $chartDepots   = [];
        $chartRetraits = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $chartLabels[]   = now()->subDays($i)->format('d/m');
            $chartDepots[]   = isset($raw[$day]) ? (float) $raw[$day]->firstWhere('type', 'DEPOT')?->total   ?? 0 : 0;
            $chartRetraits[] = isset($raw[$day]) ? (float) $raw[$day]->firstWhere('type', 'RETRAIT')?->total ?? 0 : 0;
        }

        // ── Par succursale ────────────────────────────────────────────────────
        $parBranche = DB::table('savings_accounts')
            ->leftJoin('branches', 'savings_accounts.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('COALESCE(branches.name, "Sans succursale") as branche'),
                DB::raw('COUNT(CASE WHEN savings_accounts.status = "actif" THEN 1 END) as nb_actif'),
                DB::raw('SUM(CASE WHEN savings_accounts.status = "actif" THEN savings_accounts.balance ELSE 0 END) as solde_total')
            )
            ->groupBy('savings_accounts.branch_id', 'branches.name')
            ->orderByDesc('solde_total')
            ->get();

        $mouvBranche = DB::table('savings_account_transactions')
            ->leftJoin('savings_accounts', 'savings_account_transactions.savings_account_id', '=', 'savings_accounts.id')
            ->leftJoin('branches', 'savings_accounts.branch_id', '=', 'branches.id')
            ->whereIn('savings_account_transactions.type', ['DEPOT', 'RETRAIT'])
            ->where('savings_account_transactions.created_at', '>=', now()->startOfMonth())
            ->select(
                DB::raw('COALESCE(branches.name, "Sans succursale") as branche'),
                'savings_account_transactions.type',
                DB::raw('SUM(savings_account_transactions.amount) as total')
            )
            ->groupBy('savings_accounts.branch_id', 'branches.name', 'savings_account_transactions.type')
            ->get()
            ->groupBy('branche');

        $parBranche = $parBranche->map(function ($row) use ($mouvBranche) {
            $mv = $mouvBranche->get($row->branche, collect());
            $row->depot_mois   = (float) $mv->firstWhere('type', 'DEPOT')?->total   ?? 0;
            $row->retrait_mois = (float) $mv->firstWhere('type', 'RETRAIT')?->total ?? 0;
            return $row;
        });

        // ── Alertes ───────────────────────────────────────────────────────────
        $alertes = [
            'sous_minimum'      => SavingsAccount::where('status', 'actif')->where('balance', '<', $soldeMinimum)->count(),
            'interet_non_verse' => SavingsAccount::where('status', 'actif')
                ->where(function ($q) {
                    $q->whereNull('last_interest_at')
                      ->orWhere('last_interest_at', '<', now()->startOfMonth());
                })->count(),
            'suspendus_30j'     => SavingsAccount::where('status', 'suspendu')
                ->where('updated_at', '<', now()->subDays(30))
                ->count(),
        ];

        return view('savings-accounts.dashboard', compact(
            'kpi', 'flux', 'periode', 'tauxMensuel',
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

        $base = SavingsAccountTransaction::whereBetween('created_at', [$dateDebut, $dateFin]);

        $flux = [
            'depots'             => (clone $base)->where('type', 'DEPOT')->sum('amount'),
            'depots_nb'          => (clone $base)->where('type', 'DEPOT')->count(),
            'retraits'           => (clone $base)->where('type', 'RETRAIT')->sum('amount'),
            'retraits_nb'        => (clone $base)->where('type', 'RETRAIT')->count(),
            'frais_ouverture'    => (clone $base)->where('type', 'FRAIS_OUVERTURE')->sum('amount'),
            'frais_ouverture_nb' => (clone $base)->where('type', 'FRAIS_OUVERTURE')->count(),
            'interets'           => (clone $base)->where('type', 'INTERET')->sum('amount'),
            'interets_nb'        => (clone $base)->where('type', 'INTERET')->count(),
        ];
        $flux['net']       = $flux['depots'] - $flux['retraits'];
        $flux['total_ops'] = $flux['depots_nb'] + $flux['retraits_nb'] + $flux['frais_ouverture_nb'] + $flux['interets_nb'];

        $soldeFin  = SavingsAccount::where('status', 'actif')->sum('balance');
        $ouvertures = SavingsAccount::whereBetween('created_at', [$dateDebut, $dateFin])->count();

        // Détail journalier
        $joursDetail = DB::table('savings_account_transactions')
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->select(DB::raw('DATE(created_at) as jour'), 'type', DB::raw('COUNT(*) as nb'), DB::raw('SUM(amount) as total'))
            ->groupBy('jour', 'type')
            ->orderBy('jour')
            ->get()
            ->groupBy('jour');

        // Par succursale
        $parBranche = DB::table('savings_accounts')
            ->leftJoin('branches', 'savings_accounts.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('COALESCE(branches.name, "Sans succursale") as branche'),
                DB::raw('COUNT(CASE WHEN savings_accounts.status = "actif" THEN 1 END) as nb_actif'),
                DB::raw('SUM(CASE WHEN savings_accounts.status = "actif" THEN savings_accounts.balance ELSE 0 END) as solde_total')
            )
            ->groupBy('savings_accounts.branch_id', 'branches.name')
            ->orderByDesc('solde_total')
            ->get();

        $mouvBranche = DB::table('savings_account_transactions')
            ->leftJoin('savings_accounts', 'savings_account_transactions.savings_account_id', '=', 'savings_accounts.id')
            ->leftJoin('branches', 'savings_accounts.branch_id', '=', 'branches.id')
            ->whereBetween('savings_account_transactions.created_at', [$dateDebut, $dateFin])
            ->select(
                DB::raw('COALESCE(branches.name, "Sans succursale") as branche'),
                'savings_account_transactions.type',
                DB::raw('COUNT(*) as nb'),
                DB::raw('SUM(savings_account_transactions.amount) as total')
            )
            ->groupBy('savings_accounts.branch_id', 'branches.name', 'savings_account_transactions.type')
            ->get()
            ->groupBy('branche');

        $parBranche = $parBranche->map(function ($row) use ($mouvBranche) {
            $mv = $mouvBranche->get($row->branche, collect());
            $row->depot_periode   = (float) $mv->firstWhere('type', 'DEPOT')?->total   ?? 0;
            $row->depot_nb        = (int)   $mv->firstWhere('type', 'DEPOT')?->nb       ?? 0;
            $row->retrait_periode = (float) $mv->firstWhere('type', 'RETRAIT')?->total ?? 0;
            $row->retrait_nb      = (int)   $mv->firstWhere('type', 'RETRAIT')?->nb     ?? 0;
            $row->interet_periode = (float) $mv->firstWhere('type', 'INTERET')?->total ?? 0;
            return $row;
        });

        return view('savings-accounts.report', compact(
            'dateDebut', 'dateFin', 'generated',
            'flux', 'soldeFin', 'joursDetail',
            'parBranche', 'ouvertures'
        ));
    }

    public function settings()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $settings = AppSetting::getByGroup('compte_epargne');

        return view('savings-accounts.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'sce_frais_ouverture'       => 'required|numeric|min:0',
            'sce_taux_interet_mensuel'  => 'required|numeric|min:0|max:100',
            'sce_interet_actif'         => 'nullable|boolean',
            'sce_solde_minimum'         => 'required|numeric|min:0',
            'sce_solde_minimum_interet' => 'required|numeric|min:0',
        ]);

        AppSetting::set('sce_frais_ouverture',       $validated['sce_frais_ouverture'],      'number');
        AppSetting::set('sce_taux_interet_mensuel',  $validated['sce_taux_interet_mensuel'], 'number');
        AppSetting::set('sce_interet_actif',         $request->boolean('sce_interet_actif') ? 'true' : 'false', 'boolean');
        AppSetting::set('sce_solde_minimum',         $validated['sce_solde_minimum'],        'number');
        AppSetting::set('sce_solde_minimum_interet', $validated['sce_solde_minimum_interet'], 'number');

        return redirect()
            ->route('savings-accounts.settings')
            ->with('success', 'Paramètres compte épargne mis à jour.');
    }
}
