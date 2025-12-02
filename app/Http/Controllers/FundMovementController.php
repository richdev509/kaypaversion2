<?php

namespace App\Http\Controllers;

use App\Models\FundMovement;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class FundMovementController extends Controller
{
    /**
     * Afficher la liste des mouvements de fonds
     */
    public function index(Request $request)
    {
        // Vérifier la permission
        $user = Auth::user();
        if (!$user->hasPermissionTo('fund-movements.view')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        }

        $query = FundMovement::with(['sourceBranch', 'destinationBranch', 'creator', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par branche (si l'utilisateur n'est pas admin)
        if (!$user->hasRole('admin') && $user->branch_id) {
            $query->forBranch($user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->forBranch($request->branch_id);
        }

        // Filtrer par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtrer par période
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(20);
        $branches = Branch::orderBy('name')->get();

        return view('fund-movements.index', compact('movements', 'branches'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('fund-movements.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer un mouvement de fonds.');
        }

        $branches = Branch::orderBy('name')->get();
        $userBranch = Auth::user()->branch_id;

        return view('fund-movements.create', compact('branches', 'userBranch'));
    }

    /**
     * Enregistrer un nouveau mouvement
     */
    public function store(Request $request)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('fund-movements.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer un mouvement de fonds.');
        }

        $validated = $request->validate([
            'type' => 'required|in:IN,OUT',
            'amount' => 'required|numeric|min:1',
            'source_type' => 'required|in:SUCCURSALE,BANQUE,EXTERNE,INITIAL',
            'source_branch_id' => 'nullable|exists:branches,id',
            'destination_branch_id' => 'nullable|exists:branches,id',
            'external_source' => 'nullable|string|max:255',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validation logique
        if ($validated['type'] === FundMovement::TYPE_IN) {
            // Pour une entrée, la destination doit être spécifiée
            if (empty($validated['destination_branch_id'])) {
                return back()->withErrors(['destination_branch_id' => 'La branche de destination est requise pour une entrée de fonds.']);
            }
        } else {
            // Pour une sortie, la source doit être spécifiée
            if (empty($validated['source_branch_id'])) {
                return back()->withErrors(['source_branch_id' => 'La branche source est requise pour une sortie de fonds.']);
            }

            // Vérifier que la branche source a suffisamment de fonds disponibles
            $sourceBranch = Branch::find($validated['source_branch_id']);
            if ($sourceBranch && $sourceBranch->cash_balance < $validated['amount']) {
                return back()->withErrors([
                    'amount' => "Solde insuffisant! La branche {$sourceBranch->name} dispose de " . number_format($sourceBranch->cash_balance, 2) . " HTG. Montant demandé: " . number_format($validated['amount'], 2) . " HTG."
                ])->withInput();
            }
        }

        // Si source externe ou banque, le nom doit être fourni
        if (in_array($validated['source_type'], ['BANQUE', 'EXTERNE']) && empty($validated['external_source'])) {
            return back()->withErrors(['external_source' => 'Le nom de la source externe est requis.']);
        }

        DB::beginTransaction();

        try {
            $validated['created_by'] = Auth::id();
            $validated['status'] = FundMovement::STATUS_PENDING;

            $movement = FundMovement::create($validated);

            DB::commit();

            return redirect()->route('fund-movements.show', $movement)
                ->with('success', 'Mouvement de fonds créé avec succès! Référence: ' . $movement->reference);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création du mouvement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher un mouvement spécifique
     */
    public function show(FundMovement $fundMovement)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('fund-movements.view')) {
            abort(403, 'Vous n\'avez pas la permission de voir ce mouvement de fonds.');
        }

        $fundMovement->load(['sourceBranch', 'destinationBranch', 'creator', 'approver']);

        return view('fund-movements.show', compact('fundMovement'));
    }

    /**
     * Approuver un mouvement
     */
    public function approve(FundMovement $fundMovement)
    {
        // Vérifier les permissions
        if (!Auth::user()->hasPermissionTo('fund-movements.approve')) {
            abort(403, 'Vous n\'avez pas la permission d\'approuver un mouvement de fonds.');
        }

        if (!$fundMovement->isPending()) {
            return back()->with('error', 'Ce mouvement a déjà été traité.');
        }

        DB::beginTransaction();

        try {
            /**
             * LOGIQUE DE MISE À JOUR DES SOLDES SELON LE TYPE ET LA SOURCE:
             *
             * 1. TYPE IN (Entrée de fonds):
             *    - INITIAL: Injection de fonds → +destination
             *    - BANQUE: Retrait banque → +destination
             *    - EXTERNE: Réception externe → +destination
             *    - SUCCURSALE: Transfert d'une autre branche → +destination (source déjà --)
             *
             * 2. TYPE OUT (Sortie de fonds):
             *    - SUCCURSALE: Envoi vers autre branche → -source ET +destination
             *    - BANQUE: Dépôt à la banque → -source
             *    - EXTERNE: Envoi externe → -source
             */

            // Vérifier le solde disponible pour les sorties
            if ($fundMovement->type === FundMovement::TYPE_OUT && $fundMovement->source_branch_id) {
                $sourceBranch = Branch::find($fundMovement->source_branch_id);
                if ($sourceBranch && $sourceBranch->cash_balance < $fundMovement->amount) {
                    DB::rollBack();
                    return back()->with('error',
                        "Solde insuffisant! La branche {$sourceBranch->name} dispose de " .
                        number_format($sourceBranch->cash_balance, 2) . " HTG. Montant requis: " .
                        number_format($fundMovement->amount, 2) . " HTG."
                    );
                }
            }

            // GESTION TYPE IN (Entrées)
            if ($fundMovement->type === FundMovement::TYPE_IN) {
                if ($fundMovement->destination_branch_id) {
                    // Toutes les entrées augmentent le solde de la destination
                    $destinationBranch = Branch::find($fundMovement->destination_branch_id);
                    $destinationBranch->increment('cash_balance', $fundMovement->amount);

                    Log::info("FundMovement #{$fundMovement->id} (IN - {$fundMovement->source_type}): " .
                        "+{$fundMovement->amount} HTG à {$destinationBranch->name}");
                }
            }

            // GESTION TYPE OUT (Sorties)
            if ($fundMovement->type === FundMovement::TYPE_OUT) {
                // Toujours diminuer le solde de la branche source
                if ($fundMovement->source_branch_id) {
                    $sourceBranch = Branch::find($fundMovement->source_branch_id);
                    $sourceBranch->decrement('cash_balance', $fundMovement->amount);

                    Log::info("FundMovement #{$fundMovement->id} (OUT - {$fundMovement->source_type}): " .
                        "-{$fundMovement->amount} HTG de {$sourceBranch->name}");
                }

                // Si c'est un transfert vers une AUTRE SUCCURSALE, augmenter aussi la destination
                if ($fundMovement->source_type === 'SUCCURSALE' && $fundMovement->destination_branch_id) {
                    $destinationBranch = Branch::find($fundMovement->destination_branch_id);
                    $destinationBranch->increment('cash_balance', $fundMovement->amount);

                    Log::info("FundMovement #{$fundMovement->id} (OUT vers SUCCURSALE): " .
                        "+{$fundMovement->amount} HTG à {$destinationBranch->name}");
                }
            }

            $fundMovement->update([
                'status' => FundMovement::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('fund-movements.show', $fundMovement)
                ->with('success', 'Mouvement de fonds approuvé avec succès! Les soldes ont été mis à jour.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter un mouvement
     */
    public function reject(Request $request, FundMovement $fundMovement)
    {
        // Vérifier les permissions
        if (!Auth::user()->hasPermissionTo('fund-movements.reject')) {
            abort(403, 'Vous n\'avez pas la permission de rejeter un mouvement de fonds.');
        }

        if (!$fundMovement->isPending()) {
            return back()->with('error', 'Ce mouvement a déjà été traité.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $fundMovement->update([
                'status' => FundMovement::STATUS_REJECTED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            return redirect()->route('fund-movements.show', $fundMovement)
                ->with('success', 'Mouvement de fonds rejeté.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du rejet: ' . $e->getMessage());
        }
    }
}
