<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions
        $permissions = [
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Branches
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
            // Clients
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            // Accounts
            'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.delete',
            // Transactions
            'payments.create', 'withdrawals.create',
            // Reports
            'reports.view', 'reports.generate',
            // Fund Movements (Gestion Financière)
            'fund-movements.view', 'fund-movements.create', 'fund-movements.approve', 'fund-movements.reject',
            // Dashboard Analytics
            'dashboard.view',
            // Branch Cash Management (Gestion Caisse Succursale)
            'branch-cash.view', 'branch-cash.manage',
            // Settings
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles et assigner les permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'users.view', 'users.create', 'users.edit',
            'branches.view', 'branches.edit',
            'clients.view', 'clients.create', 'clients.edit',
            'accounts.view', 'accounts.create', 'accounts.edit',
            'payments.create', 'withdrawals.create',
            'reports.view', 'reports.generate',
            'fund-movements.view', 'fund-movements.create', 'fund-movements.approve', 'fund-movements.reject',
            'dashboard.view',
            'branch-cash.view', 'branch-cash.manage',
        ]);

        $agentRole = Role::firstOrCreate(['name' => 'agent']);
        $agentRole->givePermissionTo([
            'clients.view', 'clients.create', 'clients.edit',
            'accounts.view', 'accounts.create',
            'payments.create', 'withdrawals.create',
            'reports.view', 'reports.generate',
        ]);

        $comptableRole = Role::firstOrCreate(['name' => 'comptable']);
        $comptableRole->givePermissionTo([
            'clients.view',
            'accounts.view',
            'reports.view', 'reports.generate',
            'fund-movements.view', 'fund-movements.create', 'fund-movements.approve', 'fund-movements.reject',
            'dashboard.view',
            'branch-cash.view', 'branch-cash.manage',
        ]);

        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'clients.view',
            'accounts.view',
            'reports.view',
        ]);

        // Assigner les rôles aux utilisateurs existants basé sur leur colonne 'role'
        $this->assignRolesToExistingUsers();
    }

    /**
     * Assigner les rôles Spatie aux utilisateurs basés sur la colonne 'role'
     */
    private function assignRolesToExistingUsers()
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->role && !$user->roles->count()) {
                $user->assignRole($user->role);
                $this->command->info("Rôle '{$user->role}' assigné à {$user->name}");
            }
        }
    }
}
