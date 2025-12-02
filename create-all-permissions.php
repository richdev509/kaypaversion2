<?php

/**
 * Script pour créer toutes les permissions utilisées dans l'application
 * À exécuter sur le serveur pour corriger les erreurs de permissions manquantes
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     🔐 CRÉATION COMPLÈTE DES PERMISSIONS KAYPA           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
echo "\n";

// Réinitialiser le cache des permissions
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Liste complète de TOUTES les permissions utilisées dans l'application
$allPermissions = [
    // Dashboard
    'dashboard.view',

    // Clients
    'clients.view',
    'clients.create',
    'clients.edit',
    'clients.delete',
    'view_clients',
    'create_clients',
    'edit_clients',
    'delete_clients',
    'verify_kyc',

    // Comptes (Accounts)
    'accounts.view',
    'accounts.create',
    'accounts.edit',
    'accounts.delete',
    'view_accounts',
    'create_accounts',
    'edit_accounts',
    'manage_account_status',

    // Transactions
    'transactions.view',
    'transactions.create',
    'transactions.edit',
    'transactions.delete',
    'view_transactions',
    'create_deposits',
    'create_withdrawals',
    'cancel_transactions',
    'create_adjustments',

    // Plans
    'plans.view',
    'plans.create',
    'plans.edit',
    'plans.delete',
    'manage_plans',

    // Rapports
    'reports.view',
    'reports.create',
    'reports.generate',
    'view_reports',
    'generate_reports',

    // Utilisateurs
    'users.view',
    'users.create',
    'users.edit',
    'users.delete',
    'manage_users',

    // Rôles et Permissions
    'roles.view',
    'roles.create',
    'roles.edit',
    'roles.delete',
    'manage_roles',
    'permissions.view',
    'permissions.create',
    'permissions.edit',
    'permissions.delete',
    'manage_permissions',

    // Branches
    'branches.view',
    'branches.create',
    'branches.edit',
    'branches.delete',
    'manage_branches',

    // Gestion financière (Fund Movements)
    'fund-movements.view',
    'fund-movements.create',
    'fund-movements.edit',
    'fund-movements.delete',
    'fund-movements.approve',
    'view_fund_movements',
    'create_fund_movements',
    'approve_fund_movements',

    // Caisse Succursale (Branch Cash)
    'branch-cash.view',
    'branch-cash.manage',
    'manage_branch_cash',

    // Paiements
    'payments.view',
    'payments.create',
    'payments.edit',
    'payments.delete',

    // Retraits
    'withdrawals.view',
    'withdrawals.create',
    'withdrawals.edit',
    'withdrawals.delete',
];

echo "📋 Création de " . count($allPermissions) . " permissions...\n\n";

$created = 0;
$existing = 0;

foreach ($allPermissions as $permissionName) {
    try {
        $permission = Permission::firstOrCreate(['name' => $permissionName]);

        if ($permission->wasRecentlyCreated) {
            echo "   ✓ Créée: $permissionName\n";
            $created++;
        } else {
            $existing++;
        }
    } catch (\Exception $e) {
        echo "   ❌ Erreur pour $permissionName: " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "✅ Permissions créées: $created\n";
echo "ℹ️  Permissions existantes: $existing\n";
echo "📋 Total: " . count($allPermissions) . "\n";
echo "\n";

// Assigner TOUTES les permissions au rôle admin
echo "👑 Attribution des permissions au rôle admin...\n";

try {
    $adminRole = Role::where('name', 'admin')->first();

    if ($adminRole) {
        $adminRole->syncPermissions($allPermissions);
        echo "   ✅ Toutes les permissions assignées à admin\n";
    } else {
        echo "   ⚠️  Rôle admin non trouvé\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Assigner les permissions de base aux autres rôles
echo "📝 Configuration des autres rôles...\n\n";

// Manager
try {
    $managerRole = Role::firstOrCreate(['name' => 'manager']);
    $managerPermissions = [
        'dashboard.view',
        'clients.view', 'clients.create', 'clients.edit',
        'accounts.view', 'accounts.create', 'accounts.edit',
        'transactions.view', 'create_deposits', 'create_withdrawals',
        'reports.view', 'reports.generate',
        'fund-movements.view', 'fund-movements.create',
        'branch-cash.view',
    ];
    $managerRole->syncPermissions($managerPermissions);
    echo "   ✓ Manager: " . count($managerPermissions) . " permissions\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur Manager: " . $e->getMessage() . "\n";
}

// Comptable
try {
    $comptableRole = Role::firstOrCreate(['name' => 'comptable']);
    $comptablePermissions = [
        'dashboard.view',
        'clients.view',
        'accounts.view',
        'transactions.view', 'create_deposits', 'create_withdrawals',
        'reports.view', 'reports.generate',
        'fund-movements.view',
    ];
    $comptableRole->syncPermissions($comptablePermissions);
    echo "   ✓ Comptable: " . count($comptablePermissions) . " permissions\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur Comptable: " . $e->getMessage() . "\n";
}

// Agent
try {
    $agentRole = Role::firstOrCreate(['name' => 'agent']);
    $agentPermissions = [
        'dashboard.view',
        'clients.view', 'clients.create', 'clients.edit',
        'accounts.view', 'accounts.create',
        'transactions.view', 'create_deposits', 'create_withdrawals',
    ];
    $agentRole->syncPermissions($agentPermissions);
    echo "   ✓ Agent: " . count($agentPermissions) . " permissions\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur Agent: " . $e->getMessage() . "\n";
}

// Support
try {
    $supportRole = Role::firstOrCreate(['name' => 'support']);
    $supportPermissions = [
        'dashboard.view',
        'clients.view',
        'accounts.view',
        'transactions.view',
    ];
    $supportRole->syncPermissions($supportPermissions);
    echo "   ✓ Support: " . count($supportPermissions) . " permissions\n";
} catch (\Exception $e) {
    echo "   ❌ Erreur Support: " . $e->getMessage() . "\n";
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║              ✅ PERMISSIONS CRÉÉES!                       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "🔄 Nettoyage des caches...\n";

try {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    echo "   ✓ Caches nettoyés\n";
} catch (\Exception $e) {
    echo "   ⚠️  Erreur cache: " . $e->getMessage() . "\n";
}

echo "\n";
echo "🎉 Terminé! Toutes les permissions sont maintenant disponibles.\n";
echo "   Vous pouvez maintenant utiliser l'application sans erreur de permissions.\n";
echo "\n";
