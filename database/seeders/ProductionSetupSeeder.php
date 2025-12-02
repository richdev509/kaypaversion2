<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductionSetupSeeder extends Seeder
{
    /**
     * Setup production: Spatie Permissions + Admin user
     * Sans toucher aux données existantes
     */
    public function run(): void
    {
        $this->command->info('🚀 Configuration production KAYPA...');

        // 1. Installer tables Spatie si nécessaire
        $this->setupSpatiePermissions();

        // 2. Créer rôles et permissions
        $this->createRolesAndPermissions();

        // 3. Créer utilisateur admin
        $this->createAdminUser();

        $this->command->info('✅ Configuration terminée!');
    }

    /**
     * Vérifier et installer tables Spatie Permission
     */
    protected function setupSpatiePermissions(): void
    {
        $this->command->info('📋 Vérification tables Spatie Permission...');

        try {
            // Tester si tables existent
            Role::count();
            Permission::count();
            $this->command->info('✓ Tables Spatie déjà présentes');
        } catch (\Exception $e) {
            $this->command->warn('⚠ Tables Spatie manquantes, exécuter: php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"');
            $this->command->warn('Puis: php artisan migrate');
        }
    }

    /**
     * Créer rôles et permissions
     */
    protected function createRolesAndPermissions(): void
    {
        $this->command->info('🔐 Configuration rôles et permissions...');

        // Réinitialiser cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer permissions
        $permissions = [
            // Clients
            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',
            'verify_kyc',

            // Comptes
            'view_accounts',
            'create_accounts',
            'edit_accounts',
            'manage_account_status',

            // Transactions
            'view_transactions',
            'create_deposits',
            'create_withdrawals',
            'cancel_transactions',
            'create_adjustments',

            // Rapports
            'view_reports',
            'generate_reports',

            // Administration
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_branches',
            'manage_plans',

            // Gestion financière
            'view_fund_movements',
            'create_fund_movements',
            'approve_fund_movements',
            'manage_branch_cash',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('✓ ' . count($permissions) . ' permissions créées/vérifiées');

        // Créer rôles avec permissions
        $this->createRoleWithPermissions('admin', $permissions); // Toutes permissions

        $this->createRoleWithPermissions('manager', [
            'view_clients', 'create_clients', 'edit_clients', 'verify_kyc',
            'view_accounts', 'create_accounts', 'edit_accounts',
            'view_transactions', 'create_deposits', 'create_withdrawals',
            'view_reports', 'generate_reports',
            'view_fund_movements', 'create_fund_movements',
            'manage_branch_cash',
        ]);

        $this->createRoleWithPermissions('comptable', [
            'view_clients', 'view_accounts', 'view_transactions',
            'create_deposits', 'create_withdrawals', 'cancel_transactions',
            'create_adjustments', 'view_reports', 'generate_reports',
            'view_fund_movements',
        ]);

        $this->createRoleWithPermissions('agent', [
            'view_clients', 'create_clients', 'edit_clients',
            'view_accounts', 'create_accounts',
            'view_transactions', 'create_deposits', 'create_withdrawals',
        ]);

        $this->createRoleWithPermissions('support', [
            'view_clients', 'view_accounts', 'view_transactions',
        ]);

        $this->command->info('✓ 5 rôles créés avec permissions');
    }

    /**
     * Créer rôle avec permissions
     */
    protected function createRoleWithPermissions(string $roleName, array $permissions): void
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        $role->syncPermissions($permissions);
    }

    /**
     * Créer utilisateur admin
     */
    protected function createAdminUser(): void
    {
        $this->command->info('👤 Création utilisateur administrateur...');

        // Vérifier si admin existe déjà
        $adminExists = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->exists();

        if ($adminExists) {
            $this->command->info('✓ Admin existe déjà');
            return;
        }

        // Créer admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@kaypa.ht'],
            [
                'name' => 'Administrateur KAYPA',
                'password' => Hash::make('Admin@2024!'),
                'telephone' => '+509 0000-0000',
                'branch_id' => 1, // Succursale principale
            ]
        );

        // Assigner rôle admin
        $admin->assignRole('admin');

        $this->command->info('✅ Admin créé avec succès!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📧 Email: admin@kaypa.ht');
        $this->command->info('🔑 Mot de passe: Admin@2024!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->warn('⚠️  CHANGEZ LE MOT DE PASSE IMMÉDIATEMENT!');
    }
}
