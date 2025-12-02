<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    /**
     * Matrice des permissions par rôle
     * Cette configuration peut être modifiée sans toucher à la base de données
     */
    private static array $permissions = [
        'super_admin' => [
            'view_dashboard',
            'manage_users',
            'manage_roles',
            'manage_clients',
            'manage_accounts',
            'make_deposits',
            'make_withdrawals',
            'view_reports',
            'view_financial_reports',
            'manage_plans',
            'system_settings',
            'view_audit_logs',
            'export_data',
            'delete_records',
        ],
        'admin' => [
            'view_dashboard',
            'manage_users',
            'manage_clients',
            'manage_accounts',
            'make_deposits',
            'make_withdrawals',
            'view_reports',
            'view_financial_reports',
            'manage_plans',
            'export_data',
        ],
        'directeur' => [
            'view_dashboard',
            'manage_clients',
            'manage_accounts',
            'view_reports',
            'view_financial_reports',
            'approve_large_withdrawals',
            'export_data',
        ],
        'manager' => [
            'view_dashboard',
            'manage_clients',
            'manage_accounts',
            'make_deposits',
            'make_withdrawals',
            'view_reports',
            'approve_withdrawals',
        ],
        'comptable' => [
            'view_dashboard',
            'view_clients',
            'view_accounts',
            'view_reports',
            'view_financial_reports',
            'reconciliation',
            'export_data',
        ],
        'caissier' => [
            'view_dashboard',
            'view_clients',
            'view_accounts',
            'make_deposits',
            'make_withdrawals',
            'print_receipts',
        ],
        'agent' => [
            'view_dashboard',
            'manage_clients',
            'create_accounts',
            'view_accounts',
            'make_deposits',
        ],
        'cyber-admin' => [
            'view_dashboard',
            'manage_users',
            'system_settings',
            'view_audit_logs',
            'backup_restore',
            'technical_support',
        ],
        'service-client' => [
            'view_dashboard',
            'view_clients',
            'view_accounts',
            'view_transactions',
            'customer_support',
        ],
    ];

    /**
     * Vérifie si un utilisateur a une permission spécifique
     */
    public static function hasPermission(User $user, string $permission): bool
    {
        $role = $user->role ?? 'agent';

        return in_array($permission, self::$permissions[$role] ?? []);
    }

    /**
     * Vérifie si un utilisateur a l'une des permissions spécifiées
     */
    public static function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur a toutes les permissions spécifiées
     */
    public static function hasAllPermissions(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtenir toutes les permissions d'un utilisateur
     */
    public static function getUserPermissions(User $user): array
    {
        $role = $user->role ?? 'agent';

        return self::$permissions[$role] ?? [];
    }

    /**
     * Obtenir tous les rôles disponibles
     */
    public static function getAllRoles(): array
    {
        return array_keys(self::$permissions);
    }

    /**
     * Obtenir toutes les permissions disponibles
     */
    public static function getAllPermissions(): array
    {
        $allPermissions = [];

        foreach (self::$permissions as $permissions) {
            $allPermissions = array_merge($allPermissions, $permissions);
        }

        return array_unique($allPermissions);
    }

    /**
     * Vérifie si un rôle existe
     */
    public static function roleExists(string $role): bool
    {
        return isset(self::$permissions[$role]);
    }

    /**
     * Obtenir le label d'une permission en français
     */
    public static function getPermissionLabel(string $permission): string
    {
        $labels = [
            'view_dashboard' => 'Voir le tableau de bord',
            'manage_users' => 'Gérer les utilisateurs',
            'manage_roles' => 'Gérer les rôles',
            'manage_clients' => 'Gérer les clients',
            'manage_accounts' => 'Gérer les comptes',
            'make_deposits' => 'Effectuer des dépôts',
            'make_withdrawals' => 'Effectuer des retraits',
            'view_reports' => 'Voir les rapports',
            'view_financial_reports' => 'Voir les rapports financiers',
            'manage_plans' => 'Gérer les plans',
            'system_settings' => 'Paramètres système',
            'view_audit_logs' => 'Voir les logs d\'audit',
            'export_data' => 'Exporter les données',
            'delete_records' => 'Supprimer des enregistrements',
            'approve_large_withdrawals' => 'Approuver les gros retraits',
            'approve_withdrawals' => 'Approuver les retraits',
            'view_clients' => 'Voir les clients',
            'view_accounts' => 'Voir les comptes',
            'reconciliation' => 'Réconciliation',
            'print_receipts' => 'Imprimer les reçus',
            'create_accounts' => 'Créer des comptes',
            'view_transactions' => 'Voir les transactions',
            'backup_restore' => 'Backup et restauration',
            'technical_support' => 'Support technique',
            'customer_support' => 'Support client',
        ];

        return $labels[$permission] ?? $permission;
    }
}
