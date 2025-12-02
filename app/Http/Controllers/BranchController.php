<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index(Request $request)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branches.view')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        }

        $query = Branch::query();

        // Recherche
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $branches = $query->withCount('clients')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branches.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer une branche.');
        }

        return view('branches.create');
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(Request $request)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branches.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer une branche.');
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:branches,name',
            'address' => 'nullable|string|max:255',
        ]);

        Branch::create($request->only(['name', 'address']));

        return redirect()->route('branches.index')
            ->with('success', 'Branche créée avec succès');
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branches.view')) {
            abort(403, 'Vous n\'avez pas la permission de voir cette branche.');
        }

        $branch->loadCount('clients');

        // Récupérer les clients de cette branche avec leurs comptes
        $clients = $branch->clients()
            ->withCount('accounts')
            ->with(['accounts' => function($q) {
                $q->where('status', 'actif');
            }])
            ->paginate(15);

        return view('branches.show', compact('branch', 'clients'));
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Branch $branch)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branches.edit')) {
            abort(403, 'Vous n\'avez pas la permission de modifier une branche.');
        }

        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branches.edit')) {
            abort(403, 'Vous n\'avez pas la permission de modifier une branche.');
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:branches,name,' . $branch->id,
            'address' => 'nullable|string|max:255',
        ]);

        $branch->update($request->only(['name', 'address']));

        return redirect()->route('branches.index')
            ->with('success', 'Branche mise à jour avec succès');
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy(Branch $branch)
    {
        // Vérifier la permission
        if (!Auth::user()->hasPermissionTo('branches.delete')) {
            abort(403, 'Vous n\'avez pas la permission de supprimer une branche.');
        }

        // Vérifier si la branche a des clients
        if ($branch->clients()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une branche ayant des clients');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branche supprimée avec succès');
    }
}
