<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Client;
use App\Models\Plan;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Account::with(['client', 'plan']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_id', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par dette
        if ($request->filled('has_debt')) {
            if ($request->has_debt == '1') {
                $query->where('retrait_status', 1);
            } else {
                $query->where('retrait_status', 0);
            }
        }

        $accounts = $query->latest()->paginate(20)->withQueryString();

        // Statistiques filtrées par succursale (UNIQUEMENT LES STATS)
        $statsQuery = Account::query();
        if (!in_array($user->role, ['admin', 'comptable']) && $user->branch_id) {
            $statsQuery->whereHas('client', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        $totalBalance = $statsQuery->sum('amount_after');
        $totalAccounts = $statsQuery->count();

        return view('accounts.index', compact('accounts', 'totalBalance', 'totalAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Récupérer le client depuis l'URL si fourni
        $clientId = request('client_id');
        $selectedClient = $clientId ? Client::find($clientId) : null;

        $clients = Client::orderBy('first_name')->get();
        $plans = Plan::with('montants')->get();

        return view('accounts.create', compact('clients', 'plans', 'selectedClient'));
    }

    /**
     * AJAX: Récupérer les montants disponibles pour un plan
     */
    public function getPlanMontants($planId)
    {
        $plan = Plan::with('montants')->findOrFail($planId);

        $montants = $plan->montants->map(function ($montant) use ($plan) {
            return [
                'id' => $montant->id,
                'montant_par_jour' => $montant->montant_par_jour,
                'total_prevu' => $montant->montant_par_jour * $plan->duree,
                'formatted' => number_format($montant->montant_par_jour, 0, ',', ' ') . ' HTG/jour',
                'description' => number_format($montant->montant_par_jour, 0, ',', ' ') . ' HTG/jour → Total: ' . number_format($montant->montant_par_jour * $plan->duree, 0, ',', ' ') . ' HTG',
            ];
        });

        return response()->json([
            'success' => true,
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'duree' => $plan->duree,
                'montant_ouverture' => $plan->montant_ouverture,
            ],
            'montants' => $montants,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plan_id' => 'required|exists:plans,id',
            'montant_journalier' => 'required|numeric|min:1',
            'date_debut' => 'required|date',
            'payment_method' => 'required|in:cash,moncash,bank_transfer',
        ]);

        DB::beginTransaction();

        try {
            $plan = Plan::findOrFail($validated['plan_id']);
            $client = Client::findOrFail($validated['client_id']);
            $montantJournalier = $validated['montant_journalier'];

            // Vérifier que le montant journalier existe pour ce plan
            $montantExists = $plan->montants()->where('montant_par_jour', $montantJournalier)->exists();
            if (!$montantExists) {
                return back()
                    ->withInput()
                    ->with('error', 'Le montant journalier sélectionné n\'est pas valide pour ce plan.');
            }

            // Calculer la date de fin
            $dateDebut = \Carbon\Carbon::parse($validated['date_debut']);
            $dateFin = $dateDebut->copy()->addDays($plan->duree);

            // Créer le compte avec soldes à 0
            $account = Account::create([
                'client_id' => $validated['client_id'],
                'plan_id' => $validated['plan_id'],
                'montant_journalier' => $montantJournalier,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'balance' => 0,
                'amount_after' => 0,
                'montant_dispo_retrait' => 0,
                'withdraw' => 0,
                'retrait_status' => 0,
                'status' => 'actif',
            ]);

            // Enregistrer la transaction de paiement initial (pour traçabilité uniquement)
            AccountTransaction::create([
                'account_id' => $account->account_id,
                'client_id' => $account->client_id,
                'type' => AccountTransaction::TYPE_PAIEMENT_INITIAL,
                'amount' => $montantJournalier, // Frais d'ouverture payé
                'amount_after' => 0, // Solde reste à 0 (frais non comptés dans le solde)
                'method' => $validated['payment_method'],
                'reference' => 'OUVERTURE-' . $account->account_id,
                'note' => "Paiement d'ouverture du carnet à la création - Plan: {$plan->name} ({$plan->duree} jours)",
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('accounts.show', $account)
                ->with('success', 'Compte créé avec succès! Numéro: ' . $account->account_id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du compte: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        $account->load(['client', 'plan', 'transactions' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        //
    }

    /**
     * Changer le statut d'un compte (admin et comptable uniquement)
     */
    public function updateStatus(Request $request, Account $account)
    {
        // Vérifier que l'utilisateur est admin ou comptable
        if (!in_array(Auth::user()->role, ['admin', 'comptable'])) {
            abort(403, 'Vous n\'avez pas la permission de modifier le statut d\'un compte.');
        }

        $validated = $request->validate([
            'status' => 'required|in:actif,inactif,cloture,pending',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $account->status;

        $account->update([
            'status' => $validated['status'],
        ]);

        $message = "Statut du compte mis à jour: {$oldStatus} → {$validated['status']}";
        if ($validated['reason']) {
            $message .= " (Raison: {$validated['reason']})";
        }

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        //
    }
}
