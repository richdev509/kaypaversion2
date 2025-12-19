<?php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir la liste des transferts
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'agent', 'manager', 'comptable']);
    }

    /**
     * Déterminer si l'utilisateur peut voir un transfert
     */
    public function view(User $user, Transfer $transfer): bool
    {
        if ($user->isAdmin() || $user->hasRole('comptable')) {
            return true;
        }

        // Agent/Manager peuvent voir les transferts de leur branche
        return $transfer->branch_id === $user->branch_id
            || $transfer->paid_at_branch_id === $user->branch_id;
    }

    /**
     * Déterminer si l'utilisateur peut créer un transfert
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'agent', 'manager']);
    }

    /**
     * Déterminer si l'utilisateur peut payer un transfert
     */
    public function pay(User $user, Transfer $transfer): bool
    {
        // Seuls les transferts en attente peuvent être payés
        if ($transfer->status !== 'pending') {
            return false;
        }

        return $user->hasAnyRole(['admin', 'agent', 'manager']);
    }

    /**
     * Déterminer si l'utilisateur peut annuler un transfert
     */
    public function cancel(User $user, Transfer $transfer): bool
    {
        // Seuls les transferts en attente peuvent être annulés
        if ($transfer->status !== 'pending') {
            return false;
        }

        // Seuls admin et manager peuvent annuler
        return $user->isAdmin() || $user->hasRole('manager');
    }
}
