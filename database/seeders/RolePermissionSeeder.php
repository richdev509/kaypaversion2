<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Réinitialiser le cache des rôles et permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions
        $permissions = [
            // Gestion des clients
            'view-clients',
            'create-clients',
            'edit-clients',
            'delete-clients',

            // Gestion des comptes
            'view-accounts',
            'create-accounts',
            'edit-accounts',
            'delete-accounts',
            'activate-accounts',
            'deactivate-accounts',

            // Gestion des transactions
            'view-transactions',
            'create-deposits',
            'create-withdrawals',
            'validate-transactions',
            'cancel-transactions',

            // Gestion des paiements
            'view-payments',
            'create-payments',
            'validate-payments',

            // Gestion des utilisateurs
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',

            // Rapports et statistiques
            'view-reports',
            'view-financial-reports',
            'export-reports',

            // Gestion des plans
            'view-plans',
            'create-plans',
            'edit-plans',
            'delete-plans',

            // Administration système
            'view-system-logs',
            'manage-settings',
            'backup-database',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Créer les rôles et assigner les permissions

        // 1. Super Admin - Toutes les permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin - Presque toutes les permissions (sauf backup database)
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view-clients', 'create-clients', 'edit-clients', 'delete-clients',
            'view-accounts', 'create-accounts', 'edit-accounts', 'delete-accounts',
            'activate-accounts', 'deactivate-accounts',
            'view-transactions', 'create-deposits', 'create-withdrawals',
            'validate-transactions', 'cancel-transactions',
            'view-payments', 'create-payments', 'validate-payments',
            'view-users', 'create-users', 'edit-users', 'delete-users', 'assign-roles',
            'view-reports', 'view-financial-reports', 'export-reports',
            'view-plans', 'create-plans', 'edit-plans', 'delete-plans',
            'view-system-logs', 'manage-settings',
        ]);

        // 3. Directeur - Vision globale et rapports
        $directeur = Role::create(['name' => 'directeur']);
        $directeur->givePermissionTo([
            'view-clients', 'view-accounts', 'view-transactions', 'view-payments',
            'view-reports', 'view-financial-reports', 'export-reports',
            'view-plans', 'view-users',
        ]);

        // 4. Manager - Gestion opérationnelle
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view-clients', 'create-clients', 'edit-clients',
            'view-accounts', 'create-accounts', 'edit-accounts',
            'activate-accounts', 'deactivate-accounts',
            'view-transactions', 'create-deposits', 'create-withdrawals',
            'validate-transactions',
            'view-payments', 'create-payments', 'validate-payments',
            'view-reports', 'view-financial-reports',
            'view-plans',
        ]);

        // 5. Comptable - Finances et rapports
        $comptable = Role::create(['name' => 'comptable']);
        $comptable->givePermissionTo([
            'view-clients', 'view-accounts', 'view-transactions',
            'view-payments', 'validate-payments',
            'view-reports', 'view-financial-reports', 'export-reports',
        ]);

        // 6. Caissier - Transactions quotidiennes
        $caissier = Role::create(['name' => 'caissier']);
        $caissier->givePermissionTo([
            'view-clients', 'view-accounts',
            'view-transactions', 'create-deposits', 'create-withdrawals',
            'view-payments', 'create-payments',
        ]);

        // 7. Agent - Opérations de base
        $agent = Role::create(['name' => 'agent']);
        $agent->givePermissionTo([
            'view-clients', 'create-clients', 'edit-clients',
            'view-accounts', 'create-accounts',
            'view-transactions', 'create-deposits', 'create-withdrawals',
            'view-payments',
        ]);

        // 8. Cyber Admin - Gestion utilisateurs et système
        $cyberAdmin = Role::create(['name' => 'cyber-admin']);
        $cyberAdmin->givePermissionTo([
            'view-users', 'create-users', 'edit-users', 'delete-users',
            'assign-roles', 'view-system-logs', 'manage-settings',
        ]);

        // 9. Service Client - Support client
        $serviceClient = Role::create(['name' => 'service-client']);
        $serviceClient->givePermissionTo([
            'view-clients', 'edit-clients',
            'view-accounts', 'view-transactions', 'view-payments',
        ]);
    }
}
