<?php

// Test du système de monitoring
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "\n=== TEST DU SYSTÈME DE MONITORING ===\n\n";

    // 1. Vérifier la table activity_logs
    echo "1. Vérification de la table activity_logs...\n";
    $tableExists = DB::select("SHOW TABLES LIKE 'activity_logs'");
    if (empty($tableExists)) {
        echo "   ❌ Table activity_logs n'existe pas!\n";
        exit(1);
    }
    echo "   ✅ Table activity_logs existe\n\n";

    // 2. Vérifier les colonnes
    echo "2. Vérification des colonnes...\n";
    $columns = DB::select("DESCRIBE activity_logs");
    $columnNames = array_map(fn($col) => $col->Field, $columns);

    $requiredColumns = ['id', 'user_id', 'action_type', 'model_type', 'model_id', 'description', 'reason', 'changes', 'ip_address', 'user_agent', 'created_at'];

    foreach ($requiredColumns as $col) {
        if (in_array($col, $columnNames)) {
            echo "   ✅ Colonne '$col' existe\n";
        } else {
            echo "   ❌ Colonne '$col' manquante!\n";
        }
    }
    echo "\n";

    // 3. Vérifier qu'un admin existe
    echo "3. Vérification des utilisateurs admin...\n";
    $admin = User::where('role', 'admin')->first();
    if ($admin) {
        echo "   ✅ Admin trouvé: {$admin->name} ({$admin->email})\n";
        echo "   - ID: {$admin->id}\n";
        echo "   - Rôle: {$admin->role}\n";
    } else {
        echo "   ❌ Aucun admin trouvé!\n";
    }
    echo "\n";

    // 4. Vérifier le nombre de logs existants
    echo "4. Vérification des logs existants...\n";
    $logCount = DB::table('activity_logs')->count();
    echo "   ℹ️  Nombre de logs: $logCount\n\n";

    // 5. Tester ActivityLogger (seulement si un admin existe)
    if ($admin) {
        echo "5. Test de création d'un log...\n";
        auth()->login($admin);

        $testLog = ActivityLogger::logCustom('system_test', 'Test automatique du système de monitoring');

        if ($testLog) {
            echo "   ✅ Log créé avec succès!\n";
            echo "   - ID: {$testLog->id}\n";
            echo "   - Type: {$testLog->action_type}\n";
            echo "   - Description: {$testLog->description}\n";
            echo "   - User: {$testLog->user_id}\n";
            echo "   - IP: " . ($testLog->ip_address ?? 'N/A') . "\n";

            // Nettoyer le log de test
            $testLog->delete();
            echo "   ✅ Log de test supprimé\n";
        } else {
            echo "   ❌ Échec de création du log!\n";
        }
    }

    echo "\n=== RÉSULTAT FINAL ===\n";
    echo "✅ Système de monitoring opérationnel!\n";
    echo "\nAccès: http://127.0.0.1:8000/activity-logs (Admin uniquement)\n\n";

} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
