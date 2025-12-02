<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('branch');

        // Recherche
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Filtre par branche
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $users = $query->latest()
            ->paginate(20)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get();
        $roles = User::availableRoles();

        return view('users.index', compact('users', 'branches', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $branches = Branch::orderBy('name')->get();
        $roles = User::availableRoles();

        return view('users.create', compact('branches', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'telephone' => 'nullable|string|max:15',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:' . implode(',', array_keys(User::availableRoles())),
            'branch_id' => 'required|exists:branches,id',
        ]);

        // Créer l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Colonne legacy
            'branch_id' => $request->branch_id,
        ]);

        // Assigner le rôle Spatie
        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['branch', 'createdTransactions']);

        // Statistiques de l'utilisateur
        $stats = [
            'total_transactions' => $user->createdTransactions()->count(),
            'total_deposits' => $user->createdTransactions()->where('type', 'PAIEMENT')->count(),
            'total_withdrawals' => $user->createdTransactions()->where('type', 'RETRAIT')->count(),
            'total_amount_deposits' => $user->createdTransactions()->where('type', 'PAIEMENT')->sum('amount'),
            'total_amount_withdrawals' => $user->createdTransactions()->where('type', 'RETRAIT')->sum('amount'),
        ];

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $branches = Branch::orderBy('name')->get();
        $roles = User::availableRoles();

        return view('users.edit', compact('user', 'branches', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:15',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:' . implode(',', array_keys(User::availableRoles())),
            'branch_id' => 'required|exists:branches,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'role' => $request->role, // Colonne legacy
            'branch_id' => $request->branch_id,
        ];

        // Mettre à jour le mot de passe uniquement s'il est fourni
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Synchroniser le rôle Spatie
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Empêcher de supprimer le dernier admin
        if ($user->isAdmin() && User::admins()->count() <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier administrateur');
        }

        // Empêcher de supprimer son propre compte
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès!');
    }

    /**
     * Afficher le formulaire de gestion des permissions d'un utilisateur
     */
    public function editPermissions(User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Accès réservé aux administrateurs');
        }

        // Récupérer toutes les permissions groupées
        $permissions = \Spatie\Permission\Models\Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        // Permissions du rôle de l'utilisateur
        $rolePermissions = $user->roles->first() ? $user->roles->first()->permissions->pluck('id')->toArray() : [];

        // Permissions directes de l'utilisateur
        $userDirectPermissions = $user->permissions->pluck('id')->toArray();

        return view('users.permissions', compact('user', 'permissions', 'rolePermissions', 'userDirectPermissions'));
    }

    /**
     * Mettre à jour les permissions d'un utilisateur
     */
    public function updatePermissions(Request $request, User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Accès réservé aux administrateurs');
        }

        $request->validate([
            'permissions' => 'array',
        ]);

        try {
            // Récupérer les permissions par ID et les synchroniser
            if ($request->has('permissions')) {
                $permissions = \Spatie\Permission\Models\Permission::whereIn('id', $request->permissions)->get();
                $user->syncPermissions($permissions);
            } else {
                // Si aucune permission cochée, retirer toutes les permissions directes
                $user->syncPermissions([]);
            }

            return redirect()->route('users.show', $user)
                ->with('success', 'Permissions de l\'utilisateur mises à jour avec succès!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser les permissions d'un utilisateur aux permissions de son rôle
     */
    public function resetPermissions(User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Accès réservé aux administrateurs');
        }

        try {
            // Retirer toutes les permissions directes
            // L'utilisateur utilisera uniquement les permissions de son rôle
            $user->syncPermissions([]);

            return redirect()->route('users.show', $user)
                ->with('success', 'Permissions réinitialisées. L\'utilisateur utilise maintenant les permissions du rôle ' . $user->role);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Display roles and permissions management.
     */
    public function rolesPermissions()
    {
        $this->authorize('viewAny', User::class);

        // Récupérer tous les rôles Spatie avec leurs permissions
        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();

        // Récupérer toutes les permissions disponibles
        $allPermissions = \Spatie\Permission\Models\Permission::orderBy('name')->get();

        return view('users.roles-permissions', compact('roles', 'allPermissions'));
    }
}
