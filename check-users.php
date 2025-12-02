<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 Utilisateurs dans la base de données:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$users = \App\Models\User::with('roles')->get();

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    echo sprintf(
        "ID: %d | %s | %s | Rôles: %s\n",
        $user->id,
        $user->email,
        $user->name,
        $roles ?: 'AUCUN'
    );
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total: " . $users->count() . " utilisateurs\n";

// Vérifier admin
$admins = \App\Models\User::role('admin')->get();
echo "\n👑 Admins: " . $admins->count() . "\n";

if ($admins->isEmpty()) {
    echo "⚠️  AUCUN ADMIN TROUVÉ!\n";
    echo "Exécutez: php artisan db:seed --class=ProductionSetupSeeder\n";
}
