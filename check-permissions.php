<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== VÉRIFICATION RÔLES ET PERMISSIONS ===\n\n";

// Tous les rôles
echo "RÔLES CRÉÉS:\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

echo "\n=== PERMISSIONS FUND-MOVEMENTS ===\n";
$fundPerms = Permission::where('name', 'like', 'fund-movements%')->get();
foreach ($fundPerms as $perm) {
    echo "- {$perm->name}\n";
}

echo "\n=== PERMISSIONS PAR RÔLE ===\n";
$roles = Role::with('permissions')->get();
foreach ($roles as $role) {
    echo "\n{$role->name} ({$role->permissions->count()} permissions):\n";
    foreach ($role->permissions as $permission) {
        echo "  - {$permission->name}\n";
    }
}

echo "\n=== FOCUS RÔLE COMPTABLE ===\n";
$comptable = Role::where('name', 'comptable')->first();
if ($comptable) {
    echo "Comptable a {$comptable->permissions->count()} permissions:\n";
    $fundMovementPerms = $comptable->permissions->filter(function($p) {
        return str_contains($p->name, 'fund-movements');
    });
    echo "  - Fund-movements: {$fundMovementPerms->count()} permissions\n";
    foreach ($fundMovementPerms as $perm) {
        echo "    * {$perm->name}\n";
    }
} else {
    echo "⚠️ Rôle comptable non trouvé!\n";
}

echo "\n=== FOCUS RÔLE MANAGER ===\n";
$manager = Role::where('name', 'manager')->first();
if ($manager) {
    echo "Manager a {$manager->permissions->count()} permissions:\n";
    $fundMovementPerms = $manager->permissions->filter(function($p) {
        return str_contains($p->name, 'fund-movements');
    });
    echo "  - Fund-movements: {$fundMovementPerms->count()} permissions\n";
    foreach ($fundMovementPerms as $perm) {
        echo "    * {$perm->name}\n";
    }
} else {
    echo "⚠️ Rôle manager non trouvé!\n";
}

echo "\n✅ Vérification terminée!\n";
