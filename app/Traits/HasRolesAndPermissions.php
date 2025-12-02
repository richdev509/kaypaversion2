<?php

namespace App\Traits;

trait HasRolesAndPermissions
{
    /**
     * Définition des rôles disponibles
     */
    public static function availableRoles(): array
    {
        return [
            'admin' => 'Administrateur',
            'agent' => 'Agent',
            'manager' => 'Manager',
            'support' => 'Support Client',
            'client' => 'Client',
        ];
    }

    /**
     * Permissions par rôle
     */
    public static function rolePermissions(): array
    {
        return [
            'admin' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
                'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
                'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.delete',
                'payments.create', 'withdrawals.create',
                'reports.view', 'settings.manage',
            ],
            'manager' => [
                'users.view', 'users.create', 'users.edit',
                'branches.view', 'branches.edit',
                'clients.view', 'clients.create', 'clients.edit',
                'accounts.view', 'accounts.create', 'accounts.edit',
                'payments.create', 'withdrawals.create',
                'reports.view',
            ],
            'agent' => [
                'clients.view', 'clients.create', 'clients.edit',
                'accounts.view', 'accounts.create',
                'payments.create', 'withdrawals.create',
            ],
            'support' => [
                'clients.view', 'accounts.view',
            ],
            'client' => [
                'accounts.view',
            ],
        ];
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifier si l'utilisateur a l'un des rôles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Vérifier si l'utilisateur a tous les rôles
     */
    public function hasAllRoles(array $roles): bool
    {
        return count(array_intersect($roles, [$this->role])) === count($roles);
    }

    /**
     * Vérifier si l'utilisateur a une permission
     */
    public function hasPermission($permission): bool
    {
        $rolePermissions = self::rolePermissions()[$this->role] ?? [];
        return in_array($permission, $rolePermissions);
    }

    /**
     * Vérifier si l'utilisateur a l'une des permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $rolePermissions = self::rolePermissions()[$this->role] ?? [];
        return count(array_intersect($permissions, $rolePermissions)) > 0;
    }

    /**
     * Vérifier si l'utilisateur a toutes les permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $rolePermissions = self::rolePermissions()[$this->role] ?? [];
        return count(array_intersect($permissions, $rolePermissions)) === count($permissions);
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Vérifier si l'utilisateur est agent
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Vérifier si l'utilisateur est support
     */
    public function isSupport(): bool
    {
        return $this->role === 'support';
    }

    /**
     * Vérifier si l'utilisateur est client
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Obtenir le nom du rôle en français
     */
    public function getRoleNameAttribute(): string
    {
        return self::availableRoles()[$this->role] ?? $this->role;
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur
     */
    public function getUserPermissions(): array
    {
        return self::rolePermissions()[$this->role] ?? [];
    }

    /**
     * Scope: Filtrer par rôle
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Admins seulement
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope: Agents seulement
     */
    public function scopeAgents($query)
    {
        return $query->where('role', 'agent');
    }

    /**
     * Scope: Managers seulement
     */
    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }
}
