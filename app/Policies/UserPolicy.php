<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Si l'utilisateur a la permission users.view, il peut voir tous les utilisateurs
        if ($user->hasPermissionTo('users.view')) {
            return true;
        }

        // Un utilisateur peut toujours voir son propre profil
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admin peut modifier tous les utilisateurs
        if ($user->role === 'admin') {
            return true;
        }

        // Si l'utilisateur a la permission users.edit
        if ($user->hasPermissionTo('users.edit')) {
            // Ne peut pas modifier un admin ou un manager
            if (!in_array($model->role, ['admin', 'manager'])) {
                return true;
            }
        }

        // Un utilisateur peut modifier son propre profil (infos basiques)
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Empêcher de se supprimer soi-même
        if ($user->id === $model->id) {
            return false;
        }

        // Vérifier la permission users.delete
        if ($user->hasPermissionTo('users.delete')) {
            // Ne peut pas supprimer un admin ou manager
            return !in_array($model->role, ['admin', 'manager']);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }
}
