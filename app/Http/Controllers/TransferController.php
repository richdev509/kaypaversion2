<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\TransferSetting;
use App\Models\Account;
use App\Models\Department;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransferController extends Controller
{
    /**
     * Page de recherche simple (pour tous)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if (!$user->hasAnyRole(['admin', 'agent', 'manager', 'comptable'])) {
            abort(403, 'Accès non autorisé');
        }

        // Afficher la page de recherche simple pour tous
        $transfer = null;
        $search = $request->get('search');

        if ($search) {
            $transfer = Transfer::with(['branch', 'createdBy', 'paidBy', 'paidAtBranch'])
                ->where('transfer_number', $search)
                ->first();
        }

        return view('transfers.index', compact('transfer', 'search'));
    }

    /**
     * Liste complète des transferts (admin/manager uniquement)
     */
    public function indexAll(Request $request)
    {
        $user = Auth::user();

        // Vérifier les permissions - uniquement admin et manager
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Accès non autorisé');
        }

        // Paramètres de filtrage
        $status = $request->get('status');
        $search = $request->get('search');
        $dateDebut = $request->get('date_debut', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        // Construire la requête
        $query = Transfer::with(['branch', 'createdBy', 'paidBy', 'paidAtBranch'])
            ->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        // Filtre par statut
        if ($status) {
            $query->where('status', $status);
        }

        // Filtre de recherche
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_phone', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%")
                  ->orWhere('receiver_phone', 'like', "%{$search}%");
            });
        }

        // Si pas admin, filtrer par branche
        if (!$user->isAdmin() && $user->branch_id) {
            $query->where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhere('paid_at_branch_id', $user->branch_id);
            });
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistiques
        $stats = $this->getStats($dateDebut, $dateFin, $user);

        return view('transfers.index_all', compact('transfers', 'stats', 'status', 'search', 'dateDebut', 'dateFin'));
    }

    /**
     * Formulaire de création de transfert
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['admin', 'agent', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        $settings = TransferSetting::getSettings();
        $departments = Department::all();

        return view('transfers.create', compact('settings', 'departments'));
    }

    /**
     * Enregistrer un nouveau transfert
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['admin', 'agent', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_country_code' => 'required|string|max:10',
            'sender_phone' => 'required|string',
            'sender_ninu' => 'required|digits:10',
            'sender_address' => 'nullable|string',
            'sender_department_id' => 'nullable|exists:departments,id',
            'sender_commune_id' => 'nullable|exists:communes,id',
            'sender_city_id' => 'nullable|exists:cities,id',
            'sender_account_id' => 'nullable|string|exists:accounts,account_id',

            'receiver_name' => 'required|string|max:255',
            'receiver_country_code' => 'required|string|max:10',
            'receiver_phone' => 'required|string',

            'amount' => 'required|numeric|min:500|max:75000',
            'note' => 'nullable|string|max:500',
        ]);

        $settings = TransferSetting::getSettings();

        // Vérifier les limites
        if ($validated['amount'] < $settings->min_amount) {
            return back()->withInput()->with('error', "Le montant minimum est de " . number_format($settings->min_amount, 0) . " GDS");
        }

        if ($validated['amount'] > $settings->max_amount) {
            return back()->withInput()->with('error', "Le montant maximum est de " . number_format($settings->max_amount, 0) . " GDS");
        }

        DB::beginTransaction();

        try {
            // Vérifier si l'expéditeur a un compte Kaypa
            $hasKaypaAccount = !empty($validated['sender_account_id']);

            // Calculer les frais
            $calculation = Transfer::calculateFees($validated['amount'], $hasKaypaAccount);

            // Créer le transfert
            $transfer = Transfer::create([
                'transfer_number' => Transfer::generateTransferNumber(),
                'sender_name' => $validated['sender_name'],
                'sender_country_code' => $validated['sender_country_code'],
                'sender_phone' => $validated['sender_phone'],
                'sender_ninu' => $validated['sender_ninu'],
                'sender_address' => $validated['sender_address'] ?? null,
                'sender_department_id' => $validated['sender_department_id'] ?? null,
                'sender_commune_id' => $validated['sender_commune_id'] ?? null,
                'sender_city_id' => $validated['sender_city_id'] ?? null,
                'sender_account_id' => $validated['sender_account_id'] ?? null,

                'receiver_name' => $validated['receiver_name'],
                'receiver_country_code' => $validated['receiver_country_code'],
                'receiver_phone' => $validated['receiver_phone'],

                'amount' => $validated['amount'],
                'fees' => $calculation['fees'],
                'discount' => $calculation['discount'],
                'total_amount' => $calculation['total'],

                'status' => 'pending',
                'created_by' => $user->id,
                'branch_id' => $user->branch_id,
                'note' => $validated['note'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('transfers.receipt', $transfer->id)
                ->with('success', 'Transfert créé avec succès! Numéro: ' . $transfer->transfer_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la création du transfert: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un transfert
     */
    public function show(Transfer $transfer)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['admin', 'agent', 'manager', 'comptable'])) {
            abort(403, 'Accès non autorisé');
        }

        $transfer->load(['branch', 'createdBy', 'paidBy', 'paidAtBranch', 'senderAccount']);

        return view('transfers.show', compact('transfer'));
    }

    /**
     * Formulaire de paiement du transfert
     */
    public function payForm(Transfer $transfer)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['admin', 'agent', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        if ($transfer->status !== 'pending') {
            return redirect()->route('transfers.show', $transfer)
                ->with('error', 'Ce transfert a déjà été traité');
        }

        $departments = Department::all();

        return view('transfers.pay', compact('transfer', 'departments'));
    }

    /**
     * Traiter le paiement du transfert
     */
    public function pay(Request $request, Transfer $transfer)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['admin', 'agent', 'manager'])) {
            abort(403, 'Accès non autorisé');
        }

        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Ce transfert a déjà été traité');
        }

        $validated = $request->validate([
            'receiver_ninu' => 'required|digits:10',
            'receiver_country_code_verify' => 'required|string|max:10',
            'receiver_phone_verify' => 'required|string',
            'receiver_address' => 'nullable|string',
            'receiver_department_id' => 'nullable|exists:departments,id',
            'receiver_commune_id' => 'nullable|exists:communes,id',
            'receiver_city_id' => 'nullable|exists:cities,id',
        ]);

        // Vérifier que le téléphone correspond
        if ($validated['receiver_phone_verify'] !== $transfer->receiver_phone) {
            return back()->with('error', 'Le numéro de téléphone ne correspond pas');
        }

        DB::beginTransaction();

        try {
            // Mettre à jour le transfert
            $transfer->update([
                'status' => 'paid',
                'paid_by' => $user->id,
                'paid_at_branch_id' => $user->branch_id,
                'paid_at' => now(),
                'receiver_ninu' => $validated['receiver_ninu'],
                'receiver_address' => $validated['receiver_address'] ?? null,
                'receiver_department_id' => $validated['receiver_department_id'] ?? null,
                'receiver_commune_id' => $validated['receiver_commune_id'] ?? null,
                'receiver_city_id' => $validated['receiver_city_id'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('transfers.receipt-receiver', $transfer->id)
                ->with('success', 'Paiement effectué avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Annuler un transfert
     */
    public function cancel(Request $request, Transfer $transfer)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->hasRole('manager')) {
            abort(403, 'Accès non autorisé');
        }

        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Seuls les transferts en attente peuvent être annulés');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|min:10',
        ]);

        $transfer->update([
            'status' => 'cancelled',
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return redirect()->route('transfers.index')
            ->with('success', 'Transfert annulé');
    }

    /**
     * Formulaire de modification d'un transfert
     */
    public function edit(Transfer $transfer)
    {
        $user = Auth::user();

        // Seuls les utilisateurs de la branche d'origine peuvent modifier
        if ($transfer->branch_id !== $user->branch_id && !$user->isAdmin()) {
            abort(403, 'Vous ne pouvez modifier que les transferts de votre branche');
        }

        // Seuls les transferts en attente peuvent être modifiés
        if ($transfer->status !== 'pending') {
            return redirect()->route('transfers.show', $transfer)
                ->with('error', 'Seuls les transferts en attente peuvent être modifiés');
        }

        $departments = Department::orderBy('name')->get();

        return view('transfers.edit', compact('transfer', 'departments'));
    }

    /**
     * Mettre à jour un transfert
     */
    public function update(Request $request, Transfer $transfer)
    {
        $user = Auth::user();

        // Seuls les utilisateurs de la branche d'origine peuvent modifier
        if ($transfer->branch_id !== $user->branch_id && !$user->isAdmin()) {
            abort(403, 'Vous ne pouvez modifier que les transferts de votre branche');
        }

        // Seuls les transferts en attente peuvent être modifiés
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Seuls les transferts en attente peuvent être modifiés');
        }

        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_country_code' => 'required|string|max:10',
            'sender_phone' => 'required|string|max:20',
            'receiver_name' => 'required|string|max:255',
            'receiver_country_code' => 'required|string|max:10',
            'receiver_phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:500|max:75000',
        ]);

        // Sauvegarder l'historique des modifications
        $changes = [];
        $oldValues = $transfer->only(['sender_name', 'sender_phone', 'receiver_name', 'receiver_phone', 'amount', 'fees', 'total_amount']);

        // Recalculer les frais si le montant change
        if ($transfer->amount != $validated['amount']) {
            $hasKaypaAccount = !empty($transfer->sender_account_id);
            $calculation = Transfer::calculateFees($validated['amount'], $hasKaypaAccount);

            $validated['fees'] = $calculation['fees'];
            $validated['discount'] = $calculation['discount'];
            $validated['total_amount'] = $calculation['total'];
        }

        // Comparer les modifications
        foreach (['sender_name', 'sender_phone', 'receiver_name', 'receiver_phone', 'amount'] as $field) {
            if (isset($validated[$field]) && $transfer->$field != $validated[$field]) {
                $changes[$field] = [
                    'old' => $transfer->$field,
                    'new' => $validated[$field]
                ];
            }
        }

        // Construire l'historique
        $history = json_decode($transfer->modification_history, true) ?? [];
        $history[] = [
            'modified_at' => now()->toDateTimeString(),
            'modified_by' => $user->name,
            'changes' => $changes
        ];

        $validated['modified_by'] = $user->id;
        $validated['modified_at'] = now();
        $validated['modification_history'] = json_encode($history);

        $transfer->update($validated);

        return redirect()->route('transfers.show', $transfer)
            ->with('success', 'Transfert modifié avec succès');
    }

    /**
     * Fiche de transfert pour l'expéditeur
     */
    public function receipt(Transfer $transfer)
    {
        $transfer->load(['branch', 'createdBy']);
        return view('transfers.receipt-sender', compact('transfer'));
    }

    /**
     * Fiche de transfert pour le bénéficiaire
     */
    public function receiptReceiver(Transfer $transfer)
    {
        if ($transfer->status !== 'paid') {
            abort(404);
        }

        $transfer->load(['paidAtBranch', 'paidBy']);
        return view('transfers.receipt-receiver', compact('transfer'));
    }

    /**
     * Vérifier un compte Kaypa
     */
    public function checkAccount(Request $request)
    {
        $accountId = $request->get('account_id');

        if (!$accountId) {
            return response()->json(['exists' => false]);
        }

        $account = Account::with('client')
            ->where('account_id', $accountId)
            ->where('status', 'actif')
            ->first();

        if ($account) {
            return response()->json([
                'exists' => true,
                'client_name' => $account->client->first_name . ' ' . $account->client->last_name,
                'phone' => $account->client->phone,
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Calculer les frais en temps réel
     */
    public function calculateFees(Request $request)
    {
        $amount = $request->get('amount', 0);
        $hasAccount = $request->get('has_account', false);

        if ($amount < 500 || $amount > 75000) {
            return response()->json(['error' => 'Montant invalide']);
        }

        $calculation = Transfer::calculateFees($amount, $hasAccount);

        return response()->json($calculation);
    }

    /**
     * Page des statistiques détaillées (admin et comptable)
     */
    public function stats(Request $request)
    {
        $user = Auth::user();

        // Vérifier les permissions - uniquement admin et comptable
        if (!$user->isAdmin() && !$user->hasRole('comptable')) {
            abort(403, 'Accès non autorisé');
        }

        // Paramètres de filtrage
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');

        // Requête de base
        $query = Transfer::whereBetween('created_at', [
            Carbon::parse($dateDebut)->startOfDay(),
            Carbon::parse($dateFin)->endOfDay()
        ]);

        // Si manager, forcer le filtre sur sa branche uniquement
        if (!$user->isAdmin() && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        // Filtre par branche
        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('paid_at_branch_id', $branchId);
            });
        }

        // Statistiques globales
        $stats = [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'total_amount' => (clone $query)->where('status', 'paid')->sum('amount'),
            'total_fees' => (clone $query)->where('status', 'paid')->sum('fees'),
            'total_discount' => (clone $query)->where('status', 'paid')->sum('discount'),
            'total_revenue' => (clone $query)->where('status', 'paid')->sum('total_amount'),
        ];

        // Statistiques par branche
        $statsByBranchQuery = Transfer::select(
                'branches.name as branch_name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN fees ELSE 0 END) as total_fees')
            )
            ->join('branches', 'transfers.branch_id', '=', 'branches.id')
            ->whereBetween('transfers.created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        // Filtrer par branche si manager
        if ($branchId) {
            $statsByBranchQuery->where('transfers.branch_id', $branchId);
        }

        $statsByBranch = $statsByBranchQuery
            ->groupBy('branches.id', 'branches.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Statistiques par jour (pour graphique)
        $statsByDayQuery = Transfer::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as total_amount')
            )
            ->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        // Filtrer par branche si manager
        if ($branchId) {
            $statsByDayQuery->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('paid_at_branch_id', $branchId);
            });
        }

        $statsByDay = $statsByDayQuery
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top agents
        $topAgentsQuery = Transfer::select(
                'users.name as agent_name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as total_amount')
            )
            ->join('users', 'transfers.created_by', '=', 'users.id')
            ->whereBetween('transfers.created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        // Filtrer par branche si manager
        if ($branchId) {
            $topAgentsQuery->where(function($q) use ($branchId) {
                $q->where('transfers.branch_id', $branchId)
                  ->orWhere('transfers.paid_at_branch_id', $branchId);
            });
        }

        $topAgents = $topAgentsQuery
            ->groupBy('users.id', 'users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $branches = Branch::all();

        return view('transfers.stats', compact(
            'stats',
            'statsByBranch',
            'statsByDay',
            'topAgents',
            'branches',
            'dateDebut',
            'dateFin',
            'branchId'
        ));
    }

    /**
     * Statistiques des transferts (méthode privée pour index)
     */
    private function getStats($dateDebut, $dateFin, $user)
    {
        $query = Transfer::whereBetween('created_at', [
            Carbon::parse($dateDebut)->startOfDay(),
            Carbon::parse($dateFin)->endOfDay()
        ]);

        // Filtrer par branche si pas admin
        if (!$user->isAdmin() && $user->branch_id) {
            $query->where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhere('paid_at_branch_id', $user->branch_id);
            });
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'total_amount' => (clone $query)->where('status', 'paid')->sum('amount'),
            'total_fees' => (clone $query)->where('status', 'paid')->sum('fees'),
        ];
    }

    /**
     * Page de paramétrage des transferts (admin uniquement)
     */
    public function settings()
    {
        $user = Auth::user();

        // Uniquement les admins
        if (!$user->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $settings = TransferSetting::first();

        if (!$settings) {
            // Créer les paramètres par défaut si inexistants
            $settings = TransferSetting::create([
                'min_amount' => 500,
                'max_amount' => 75000,
                'transfer_fee_percentage' => 0,
                'transfer_fee_fixed' => 100,
                'kaypa_client_discount' => 10,
                'is_active' => true,
            ]);
        }

        return view('transfers.settings', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres des transferts
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        // Uniquement les admins
        if (!$user->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'transfer_fee_percentage' => 'required|numeric|min:0|max:100',
            'transfer_fee_fixed' => 'required|numeric|min:0',
            'kaypa_client_discount' => 'required|numeric|min:0|max:100',
        ], [
            'min_amount.required' => 'Le montant minimum est requis',
            'max_amount.required' => 'Le montant maximum est requis',
            'max_amount.gt' => 'Le montant maximum doit être supérieur au montant minimum',
            'transfer_fee_percentage.required' => 'Le pourcentage de frais est requis',
            'transfer_fee_fixed.required' => 'Les frais fixes sont requis',
            'kaypa_client_discount.required' => 'La réduction client Kaypa est requise',
        ]);

        $settings = TransferSetting::first();

        if (!$settings) {
            $settings = new TransferSetting();
        }

        $settings->fill($validated);
        $settings->save();

        return redirect()->route('transfers.settings')
            ->with('success', 'Paramètres mis à jour avec succès');
    }

    /**
     * Page des rapports de transfert (admin et comptable uniquement)
     */
    public function reports(Request $request)
    {
        $user = Auth::user();

        // Uniquement admin et comptable
        if (!$user->isAdmin() && !$user->hasRole('comptable')) {
            abort(403, 'Accès non autorisé');
        }

        // Paramètres de filtrage
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status');
        $branchId = $request->get('branch_id');

        // Construire la requête
        $query = Transfer::with(['branch', 'createdBy', 'paidBy', 'paidAtBranch'])
            ->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        // Filtre par statut
        if ($status) {
            $query->where('status', $status);
        }

        // Filtre par branche
        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('paid_at_branch_id', $branchId);
            });
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(50);

        // Statistiques du rapport
        $stats = [
            'total_count' => $query->count(),
            'total_amount' => (clone $query)->sum('amount'),
            'total_fees' => (clone $query)->sum('fees'),
            'total_revenue' => (clone $query)->sum('total_amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'paid_count' => (clone $query)->where('status', 'paid')->count(),
            'cancelled_count' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $branches = Branch::all();

        return view('transfers.reports', compact(
            'transfers',
            'stats',
            'branches',
            'dateDebut',
            'dateFin',
            'status',
            'branchId'
        ));
    }

    /**
     * Exporter le rapport en Excel
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        // Uniquement admin et comptable
        if (!$user->isAdmin() && !$user->hasRole('comptable')) {
            abort(403, 'Accès non autorisé');
        }

        // Paramètres de filtrage
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status');
        $branchId = $request->get('branch_id');

        // Construire la requête
        $query = Transfer::with(['branch', 'createdBy', 'paidBy', 'paidAtBranch'])
            ->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('paid_at_branch_id', $branchId);
            });
        }

        $transfers = $query->orderBy('created_at', 'desc')->get();

        $filename = 'rapport_transferts_' . Carbon::parse($dateDebut)->format('Ymd') . '_' . Carbon::parse($dateFin)->format('Ymd') . '.csv';

        // Créer l'export CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        $callback = function() use ($transfers) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes
            fputcsv($file, [
                'Numéro',
                'Date',
                'Expéditeur',
                'Téléphone Exp.',
                'Bénéficiaire',
                'Téléphone Bén.',
                'Montant (GDS)',
                'Frais (GDS)',
                'Total (GDS)',
                'Statut',
                'Agent',
                'Branche Création',
                'Branche Paiement',
                'Date Paiement'
            ], ';');

            // Données
            foreach ($transfers as $transfer) {
                $status = [
                    'pending' => 'En attente',
                    'paid' => 'Payé',
                    'cancelled' => 'Annulé'
                ];

                fputcsv($file, [
                    $transfer->transfer_number,
                    $transfer->created_at->format('d/m/Y H:i'),
                    $transfer->sender_name,
                    $transfer->sender_country_code . ' ' . $transfer->sender_phone,
                    $transfer->receiver_name,
                    $transfer->receiver_country_code . ' ' . $transfer->receiver_phone,
                    number_format($transfer->amount, 2, ',', ' '),
                    number_format($transfer->fees, 2, ',', ' '),
                    number_format($transfer->total_amount, 2, ',', ' '),
                    $status[$transfer->status] ?? $transfer->status,
                    $transfer->createdBy->name ?? 'N/A',
                    $transfer->branch->name ?? 'N/A',
                    $transfer->paidAtBranch->name ?? 'N/A',
                    $transfer->paid_at ? $transfer->paid_at->format('d/m/Y H:i') : 'N/A'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter le rapport en PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        // Uniquement admin et comptable
        if (!$user->isAdmin() && !$user->hasRole('comptable')) {
            abort(403, 'Accès non autorisé');
        }

        // Paramètres de filtrage
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status');
        $branchId = $request->get('branch_id');

        // Construire la requête
        $query = Transfer::with(['branch', 'createdBy', 'paidBy', 'paidAtBranch'])
            ->whereBetween('created_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($branchId) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('paid_at_branch_id', $branchId);
            });
        }

        $transfers = $query->orderBy('created_at', 'desc')->get();

        // Statistiques
        $stats = [
            'total_count' => $transfers->count(),
            'total_amount' => $transfers->sum('amount'),
            'total_fees' => $transfers->sum('fees'),
            'total_revenue' => $transfers->sum('total_amount'),
            'pending_count' => $transfers->where('status', 'pending')->count(),
            'paid_count' => $transfers->where('status', 'paid')->count(),
            'cancelled_count' => $transfers->where('status', 'cancelled')->count(),
        ];

        $branch = $branchId ? Branch::find($branchId) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transfers.reports-pdf', compact(
            'transfers',
            'stats',
            'dateDebut',
            'dateFin',
            'status',
            'branch'
        ));

        $pdf->setPaper('a4', 'landscape');

        $filename = 'rapport_transferts_' . Carbon::parse($dateDebut)->format('Ymd') . '_' . Carbon::parse($dateFin)->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Page de gestion des litiges (admin et manager uniquement)
     */
    public function disputes(Request $request)
    {
        $user = Auth::user();

        // Uniquement admin et manager
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Accès non autorisé');
        }

        // Paramètres de filtrage
        $disputeStatus = $request->get('dispute_status');
        $dateDebut = $request->get('date_debut', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));

        // Construire la requête
        $query = Transfer::with(['branch', 'createdBy', 'paidBy', 'paidAtBranch', 'disputedBy', 'resolvedBy'])
            ->where('is_disputed', true)
            ->whereBetween('disputed_at', [
                Carbon::parse($dateDebut)->startOfDay(),
                Carbon::parse($dateFin)->endOfDay()
            ]);

        // Filtre par statut de litige
        if ($disputeStatus) {
            $query->where('dispute_status', $disputeStatus);
        }

        // Si manager, filtrer par branche
        if (!$user->isAdmin() && $user->branch_id) {
            $query->where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhere('paid_at_branch_id', $user->branch_id);
            });
        }

        $disputes = $query->orderBy('disputed_at', 'desc')->paginate(20);

        // Statistiques
        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('dispute_status', 'pending')->count(),
            'investigating' => (clone $query)->where('dispute_status', 'investigating')->count(),
            'resolved' => (clone $query)->where('dispute_status', 'resolved')->count(),
            'rejected' => (clone $query)->where('dispute_status', 'rejected')->count(),
        ];

        return view('transfers.disputes', compact('disputes', 'stats', 'disputeStatus', 'dateDebut', 'dateFin'));
    }

    /**
     * Créer un litige pour un transfert
     */
    public function createDispute(Request $request, Transfer $transfer)
    {
        $user = Auth::user();

        // Vérifier que le transfert n'est pas déjà en litige
        if ($transfer->is_disputed) {
            return back()->with('error', 'Ce transfert est déjà en litige');
        }

        $request->validate([
            'dispute_reason' => 'required|string|min:10',
        ]);

        $transfer->update([
            'is_disputed' => true,
            'dispute_status' => 'pending',
            'dispute_reason' => $request->dispute_reason,
            'disputed_by' => $user->id,
            'disputed_at' => now(),
        ]);

        return redirect()->route('transfers.disputes')
            ->with('success', 'Litige créé avec succès');
    }

    /**
     * Mettre à jour le statut d'un litige
     */
    public function updateDisputeStatus(Request $request, Transfer $transfer)
    {
        $user = Auth::user();

        // Uniquement admin et manager
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Accès non autorisé');
        }

        $request->validate([
            'dispute_status' => 'required|in:pending,investigating,resolved,rejected',
            'dispute_resolution' => 'required_if:dispute_status,resolved,rejected|string|min:10',
        ]);

        $data = [
            'dispute_status' => $request->dispute_status,
        ];

        // Si résolu ou rejeté, ajouter la résolution
        if (in_array($request->dispute_status, ['resolved', 'rejected'])) {
            $data['dispute_resolution'] = $request->dispute_resolution;
            $data['resolved_by'] = $user->id;
            $data['resolved_at'] = now();
        }

        $transfer->update($data);

        return back()->with('success', 'Statut du litige mis à jour');
    }

    /**
     * Afficher les détails d'un litige
     */
    public function showDispute(Transfer $transfer)
    {
        $user = Auth::user();

        // Uniquement admin et manager
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que le transfert est en litige
        if (!$transfer->is_disputed) {
            return redirect()->route('transfers.show', $transfer)
                ->with('error', 'Ce transfert n\'est pas en litige');
        }

        $transfer->load(['branch', 'createdBy', 'paidBy', 'paidAtBranch', 'disputedBy', 'resolvedBy']);

        return view('transfers.dispute-show', compact('transfer'));
    }
}
