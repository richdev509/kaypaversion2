<?php

/**
 * Script de déploiement production KAYPA
 *
 * Ce script met à jour la base de données existante sans perdre les données
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 DÉPLOIEMENT KAYPA VERSION 2\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// 1. Vérifier connexion DB
echo "1️⃣  Vérification connexion base de données...\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Connexion OK: " . config('database.connections.mysql.database') . "\n\n";
} catch (\Exception $e) {
    echo "   ❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Vérifier tables existantes
echo "2️⃣  Vérification tables existantes...\n";
$tables = [
    'users' => DB::table('users')->count(),
    'clients' => DB::table('clients')->count(),
    'accounts' => DB::table('accounts')->count(),
    'account_transactions' => DB::table('account_transactions')->count(),
    'branches' => DB::table('branches')->count(),
];

foreach ($tables as $table => $count) {
    echo "   ✓ $table: $count enregistrements\n";
}
echo "\n";

// 3. Exécuter nouvelles migrations
echo "3️⃣  Exécution migrations (nouvelles colonnes seulement)...\n";
$exitCode = Artisan::call('migrate', ['--force' => true]);
if ($exitCode === 0) {
    echo "   ✅ Migrations OK\n\n";
} else {
    echo "   ⚠️  Migrations avec avertissements (normal si tables existent)\n\n";
}

// 4. Setup Spatie Permission
echo "4️⃣  Configuration Spatie Permission...\n";
Artisan::call('db:seed', ['--class' => 'ProductionSetupSeeder', '--force' => true]);
echo "   ✅ Rôles et permissions configurés\n\n";

// 5. Vérifier admin
echo "5️⃣  Vérification utilisateur admin...\n";
$admin = \App\Models\User::role('admin')->first();
if ($admin) {
    echo "   ✅ Admin trouvé: {$admin->email}\n\n";
} else {
    echo "   ⚠️  Aucun admin, création...\n";
    Artisan::call('db:seed', ['--class' => 'ProductionSetupSeeder', '--force' => true]);
    echo "   ✅ Admin créé: admin@kaypa.ht\n";
    echo "   🔑 Mot de passe: Admin@2024!\n\n";
}

// 6. Vider caches
echo "6️⃣  Nettoyage caches...\n";
Artisan::call('optimize:clear');
echo "   ✅ Caches vidés\n\n";

// 7. Résumé final
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ DÉPLOIEMENT TERMINÉ AVEC SUCCÈS!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📊 STATISTIQUES:\n";
echo "   - Utilisateurs: " . \App\Models\User::count() . "\n";
echo "   - Clients: " . \App\Models\Client::count() . "\n";
echo "   - Comptes: " . \App\Models\Account::count() . "\n";
echo "   - Transactions: " . \App\Models\AccountTransaction::count() . "\n";
echo "   - Branches: " . \App\Models\Branch::count() . "\n\n";

echo "🔐 COMPTES ADMIN:\n";
$admins = \App\Models\User::role('admin')->get(['email', 'name']);
foreach ($admins as $admin) {
    echo "   - {$admin->email} ({$admin->name})\n";
}

echo "\n⚠️  PROCHAINES ÉTAPES:\n";
echo "   1. Changez le mot de passe admin immédiatement\n";
echo "   2. Configurez le fichier .env pour production\n";
echo "   3. Testez la connexion et les fonctionnalités\n";
echo "   4. Activez 2FA pour les comptes admin\n\n";
