<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * Liste des rôles
     */
    public function indexRoles()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Accès réservé aux administrateurs');
        }

        $roles = Role::withCount('permissions', 'users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Formulaire création rôle
     */
    public function createRole()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Enregistrer nouveau rôle
     */
    public function storeRole(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name]);

            if ($request->has('permissions')) {
                // Convertir les IDs en objets Permission
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();
            return redirect()->route('admin.roles.index')
                ->with('success', "Rôle '{$request->name}' créé avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Formulaire édition rôle
     */
    public function editRole(Role $role)
    {
        if (!Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Mettre à jour rôle
     */
    public function updateRole(Request $request, Role $role)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Protéger le rôle admin
        if ($role->name === 'admin') {
            return back()->with('error', 'Le rôle admin ne peut pas être modifié.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $role->update(['name' => $request->name]);

            // Synchroniser les permissions (convertir IDs en objets)
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();
            return redirect()->route('admin.roles.index')
                ->with('success', "Rôle '{$request->name}' mis à jour avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Supprimer rôle
     */
    public function destroyRole(Role $role)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Protéger les rôles système
        if (in_array($role->name, ['admin', 'manager', 'agent'])) {
            return back()->with('error', 'Les rôles système ne peuvent pas être supprimés.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer ce rôle car il est assigné à des utilisateurs.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }

    /**
     * Liste des permissions
     */
    public function indexPermissions()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $permissions = Permission::withCount('roles')->orderBy('name')->get()
            ->groupBy(function($permission) {
                return explode('.', $permission->name)[0];
            });

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Créer permission
     */
    public function storePermission(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        return back()->with('success', "Permission '{$request->name}' créée avec succès!");
    }

    /**
     * Supprimer permission
     */
    public function destroyPermission(Permission $permission)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $permission->delete();
        return back()->with('success', 'Permission supprimée avec succès.');
    }
}
