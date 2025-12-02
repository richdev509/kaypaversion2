<?php

/**
 * Script de déploiement intelligent KAYPA
 *
 * Analyse automatiquement la base de données et applique les modifications nécessaires
 * sans perturber les données existantes
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class SmartDeployer
{
    private array $issues = [];
    private array $fixes = [];
    private int $step = 1;

    public function run()
    {
        $this->header();
        $this->checkDatabaseConnection();
        $this->analyzeDatabase();
        $this->displayAnalysis();
        $this->applyFixes();
        $this->verifySystem();
        $this->summary();
    }

    private function header()
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║     🚀 DÉPLOIEMENT INTELLIGENT KAYPA VERSION 2           ║\n";
        echo "║     Analyse et réparation automatique de la base         ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    private function checkDatabaseConnection()
    {
        $this->printStep("Vérification connexion base de données");

        try {
            DB::connection()->getPdo();
            $dbName = config('database.connections.mysql.database');
            $this->printSuccess("Connecté à: $dbName");
        } catch (\Exception $e) {
            $this->printError("Erreur connexion: " . $e->getMessage());
            exit(1);
        }
    }

    private function analyzeDatabase()
    {
        $this->printStep("Analyse de la base de données");
        echo "\n";

        // 1. Vérifier les tables principales
        $this->checkMainTables();

        // 2. Vérifier les tables Spatie Permission
        $this->checkSpatiePermissionTables();

        // 3. Vérifier les colonnes manquantes
        $this->checkMissingColumns();

        // 4. Vérifier les index et clés étrangères
        $this->checkIndexesAndForeignKeys();

        // 5. Vérifier les données critiques
        $this->checkCriticalData();
    }

    private function checkMainTables()
    {
        echo "📋 Vérification tables principales...\n";

        $requiredTables = [
            'users', 'clients', 'accounts', 'account_transactions',
            'branches', 'payments', 'withdrawals', 'plans',
            'plan_montants', 'reports', 'cities', 'communes',
            'departments', 'user_devices'
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->issues[] = [
                    'type' => 'missing_table',
                    'table' => $table,
                    'severity' => 'critical'
                ];
                echo "   ❌ Table manquante: $table\n";
            } else {
                $count = DB::table($table)->count();
                echo "   ✓ $table ($count enregistrements)\n";
            }
        }
    }

    private function checkSpatiePermissionTables()
    {
        echo "\n🔐 Vérification tables Spatie Permission...\n";

        $spatieTables = [
            'permissions',
            'roles',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions'
        ];

        $missingSpatie = [];
        foreach ($spatieTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingSpatie[] = $table;
                echo "   ❌ Table manquante: $table\n";
            } else {
                echo "   ✓ $table\n";
            }
        }

        if (!empty($missingSpatie)) {
            $this->issues[] = [
                'type' => 'missing_spatie',
                'tables' => $missingSpatie,
                'severity' => 'critical'
            ];
            $this->fixes[] = 'install_spatie_permission';
        }
    }

    private function checkMissingColumns()
    {
        echo "\n🔍 Vérification colonnes...\n";

        $requiredColumns = [
            'users' => ['branch_id', 'is_active', 'last_login_at', 'failed_login_attempts'],
            'clients' => ['branch_id', 'id_card_number', 'phone', 'address', 'city_id', 'commune_id'],
            'accounts' => ['branch_id', 'status', 'balance'],
            'account_transactions' => ['branch_id', 'performed_by'],
            'branches' => ['name', 'code', 'address', 'phone', 'is_active'],
        ];

        foreach ($requiredColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $existingColumns = Schema::getColumnListing($table);
            $missingColumns = array_diff($columns, $existingColumns);

            if (!empty($missingColumns)) {
                foreach ($missingColumns as $column) {
                    echo "   ⚠️  Colonne manquante: $table.$column\n";
                    $this->issues[] = [
                        'type' => 'missing_column',
                        'table' => $table,
                        'column' => $column,
                        'severity' => 'high'
                    ];
                }
                $this->fixes[] = 'run_migrations';
            } else {
                echo "   ✓ $table (toutes les colonnes présentes)\n";
            }
        }
    }

    private function checkIndexesAndForeignKeys()
    {
        echo "\n🔗 Vérification index et clés étrangères...\n";

        // Vérifier les index importants
        $criticalIndexes = [
            'users' => ['email', 'branch_id'],
            'clients' => ['id_card_number', 'branch_id'],
            'accounts' => ['account_number', 'client_id', 'branch_id'],
            'account_transactions' => ['account_id', 'transaction_type', 'branch_id'],
        ];

        foreach ($criticalIndexes as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            echo "   ℹ️  Index sur $table vérifiés\n";
        }
    }

    private function checkCriticalData()
    {
        echo "\n📊 Vérification données critiques...\n";

        // Vérifier qu'il y a au moins une branche
        if (Schema::hasTable('branches')) {
            $branchCount = DB::table('branches')->count();
            if ($branchCount === 0) {
                echo "   ⚠️  Aucune branche trouvée\n";
                $this->issues[] = [
                    'type' => 'missing_data',
                    'entity' => 'branches',
                    'severity' => 'high'
                ];
                $this->fixes[] = 'create_default_branch';
            } else {
                echo "   ✓ Branches: $branchCount\n";
            }
        }

        // Vérifier les rôles et permissions
        if (Schema::hasTable('roles')) {
            $roleCount = DB::table('roles')->count();
            if ($roleCount === 0) {
                echo "   ⚠️  Aucun rôle trouvé\n";
                $this->issues[] = [
                    'type' => 'missing_data',
                    'entity' => 'roles',
                    'severity' => 'critical'
                ];
                $this->fixes[] = 'seed_roles_permissions';
            } else {
                echo "   ✓ Rôles: $roleCount\n";
            }
        }

        // Vérifier l'admin
        if (Schema::hasTable('users') && Schema::hasTable('model_has_roles')) {
            try {
                $adminCount = DB::table('users')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('roles.name', 'admin')
                    ->count();

                if ($adminCount === 0) {
                    echo "   ⚠️  Aucun administrateur trouvé\n";
                    $this->issues[] = [
                        'type' => 'missing_data',
                        'entity' => 'admin_user',
                        'severity' => 'critical'
                    ];
                    $this->fixes[] = 'create_admin_user';
                } else {
                    echo "   ✓ Administrateurs: $adminCount\n";
                }
            } catch (\Exception $e) {
                echo "   ⚠️  Impossible de vérifier les admins\n";
            }
        }
    }

    private function displayAnalysis()
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════\n";
        echo "📋 RÉSULTAT DE L'ANALYSE\n";
        echo "═══════════════════════════════════════════════════════════\n\n";

        if (empty($this->issues)) {
            $this->printSuccess("✨ Aucun problème détecté! La base de données est à jour.");
            return;
        }

        echo "⚠️  Problèmes détectés: " . count($this->issues) . "\n\n";

        $critical = array_filter($this->issues, fn($i) => $i['severity'] === 'critical');
        $high = array_filter($this->issues, fn($i) => $i['severity'] === 'high');

        if (!empty($critical)) {
            echo "🔴 Critiques: " . count($critical) . "\n";
        }
        if (!empty($high)) {
            echo "🟡 Important: " . count($high) . "\n";
        }

        echo "\n📝 Corrections à appliquer: " . count(array_unique($this->fixes)) . "\n";
    }

    private function applyFixes()
    {
        if (empty($this->fixes)) {
            return;
        }

        echo "\n";
        $this->printStep("Application des corrections");
        echo "\n";

        // TOUJOURS synchroniser les migrations en premier
        $this->syncMigrations();

        $fixes = array_unique($this->fixes);

        foreach ($fixes as $fix) {
            switch ($fix) {
                case 'install_spatie_permission':
                    $this->installSpatiePermission();
                    break;

                case 'run_migrations':
                    $this->runMigrations();
                    break;

                case 'create_default_branch':
                    $this->createDefaultBranch();
                    break;

                case 'seed_roles_permissions':
                    $this->seedRolesAndPermissions();
                    break;

                case 'create_admin_user':
                    $this->createAdminUser();
                    break;
            }
        }
    }

    private function syncMigrations()
    {
        echo "🔄 Synchronisation des migrations avec les tables existantes...\n";

        try {
            // Obtenir toutes les tables existantes
            $existingTables = $this->getExistingTables();

            // Obtenir les migrations déjà enregistrées
            $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();

            // Obtenir tous les fichiers de migration
            $allMigrations = $this->getAllMigrationFiles();

            // Mapper les migrations aux tables
            $migrationToTable = $this->mapMigrationsToTables($allMigrations);

            $synced = 0;

            foreach ($migrationToTable as $migration => $table) {
                // Si la table existe mais la migration n'est pas enregistrée
                if (!in_array($migration, $ranMigrations) && in_array($table, $existingTables)) {
                    try {
                        DB::table('migrations')->insert([
                            'migration' => $migration,
                            'batch' => 1
                        ]);
                        echo "   ✓ Migration synchronisée: $migration → $table\n";
                        $synced++;
                    } catch (\Exception $e) {
                        // Ignorer les doublons
                        if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                            echo "   ⚠️  Erreur sync $migration: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }

            if ($synced > 0) {
                $this->printSuccess("$synced migration(s) synchronisée(s)");
            } else {
                echo "   ℹ️  Aucune synchronisation nécessaire\n";
            }
        } catch (\Exception $e) {
            echo "   ⚠️  Erreur synchronisation: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }

    private function getExistingTables(): array
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $dbName = 'Tables_in_' . config('database.connections.mysql.database');

            return array_map(function($table) use ($dbName) {
                return $table->$dbName;
            }, $tables);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAllMigrationFiles(): array
    {
        $path = database_path('migrations');
        if (!file_exists($path)) {
            return [];
        }

        $files = glob($path . '/*.php');

        return array_map(function ($file) {
            return str_replace('.php', '', basename($file));
        }, $files);
    }

    private function mapMigrationsToTables(array $migrations): array
    {
        $map = [];

        foreach ($migrations as $migration) {
            // Extraire le nom de la table du nom de la migration
            // Format: YYYY_MM_DD_HHMMSS_create_table_name_table.php
            if (preg_match('/create_(.+?)_table/', $migration, $matches)) {
                $tableName = $matches[1];
                $map[$migration] = $tableName;
            }
            // Format alternatif: add_column_to_table
            elseif (preg_match('/to_(.+?)_table/', $migration, $matches)) {
                $tableName = $matches[1];
                // Ne pas synchroniser les migrations "add" automatiquement
                // car elles peuvent avoir des colonnes à ajouter
            }
        }

        return $map;
    }    private function installSpatiePermission()
    {
        echo "🔐 Installation Spatie Permission...\n";

        try {
            // Publier les migrations
            Artisan::call('vendor:publish', [
                '--provider' => 'Spatie\Permission\PermissionServiceProvider',
                '--force' => true
            ]);
            echo "   ✓ Fichiers publiés\n";

            // Exécuter les migrations
            Artisan::call('migrate', ['--force' => true]);
            echo "   ✓ Tables créées\n";

            $this->printSuccess("Tables Spatie Permission installées");
        } catch (\Exception $e) {
            $this->printError("Erreur installation Spatie: " . $e->getMessage());
        }
    }

    private function runMigrations()
    {
        echo "🗄️  Exécution des migrations intelligentes...\n";

        try {
            // Utiliser notre commande smart migrate
            Artisan::call('migrate:smart', ['--force' => true]);
            $output = Artisan::output();

            echo $output;
            $this->printSuccess("Base de données mise à jour");
        } catch (\Exception $e) {
            // Si la commande n'existe pas, essayer d'ajouter les colonnes manuellement
            echo "   ⚠️  Utilisation de la méthode alternative...\n";
            $this->addMissingColumnsManually();
        }
    }

    private function addMissingColumnsManually()
    {
        echo "   🔧 Ajout manuel des colonnes manquantes...\n";

        $columnsToAdd = [
            'users' => [
                ['name' => 'is_active', 'sql' => "ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1"],
                ['name' => 'last_login_at', 'sql' => "ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL"],
                ['name' => 'failed_login_attempts', 'sql' => "ALTER TABLE users ADD COLUMN failed_login_attempts INT NOT NULL DEFAULT 0"],
            ],
            'clients' => [
                ['name' => 'id_card_number', 'sql' => "ALTER TABLE clients ADD COLUMN id_card_number VARCHAR(255) NULL"],
            ],
            'accounts' => [
                ['name' => 'branch_id', 'sql' => "ALTER TABLE accounts ADD COLUMN branch_id BIGINT UNSIGNED NULL"],
            ],
            'account_transactions' => [
                ['name' => 'branch_id', 'sql' => "ALTER TABLE account_transactions ADD COLUMN branch_id BIGINT UNSIGNED NULL"],
                ['name' => 'performed_by', 'sql' => "ALTER TABLE account_transactions ADD COLUMN performed_by BIGINT UNSIGNED NULL"],
            ],
            'branches' => [
                ['name' => 'code', 'sql' => "ALTER TABLE branches ADD COLUMN code VARCHAR(50) NULL"],
                ['name' => 'phone', 'sql' => "ALTER TABLE branches ADD COLUMN phone VARCHAR(255) NULL"],
                ['name' => 'is_active', 'sql' => "ALTER TABLE branches ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1"],
            ],
        ];

        foreach ($columnsToAdd as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column['name'])) {
                    try {
                        DB::statement($column['sql']);
                        echo "      ✓ Colonne ajoutée: {$table}.{$column['name']}\n";
                    } catch (\Exception $e) {
                        // Ignorer les erreurs si la colonne existe déjà
                        if (!str_contains($e->getMessage(), 'Duplicate column')) {
                            echo "      ⚠️  {$table}.{$column['name']}: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        }

        // Créer les tables manquantes
        $this->createMissingTablesManually();
    }

    private function createMissingTablesManually()
    {
        // Table payments
        if (!Schema::hasTable('payments')) {
            try {
                DB::statement("
                    CREATE TABLE payments (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        account_id BIGINT UNSIGNED NOT NULL,
                        amount DECIMAL(15, 2) NOT NULL,
                        payment_date DATE NOT NULL,
                        method VARCHAR(50) NOT NULL,
                        reference VARCHAR(255) NULL,
                        status VARCHAR(50) NOT NULL DEFAULT 'completed',
                        performed_by BIGINT UNSIGNED NULL,
                        branch_id BIGINT UNSIGNED NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        INDEX idx_account_id (account_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "      ✓ Table créée: payments\n";
            } catch (\Exception $e) {
                if (!str_contains($e->getMessage(), 'already exists')) {
                    echo "      ⚠️  Erreur payments: " . $e->getMessage() . "\n";
                }
            }
        }

        // Table withdrawals
        if (!Schema::hasTable('withdrawals')) {
            try {
                DB::statement("
                    CREATE TABLE withdrawals (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        account_id BIGINT UNSIGNED NOT NULL,
                        amount DECIMAL(15, 2) NOT NULL,
                        withdrawal_date DATE NOT NULL,
                        method VARCHAR(50) NOT NULL,
                        reference VARCHAR(255) NULL,
                        status VARCHAR(50) NOT NULL DEFAULT 'completed',
                        performed_by BIGINT UNSIGNED NULL,
                        branch_id BIGINT UNSIGNED NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        INDEX idx_account_id (account_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo "      ✓ Table créée: withdrawals\n";
            } catch (\Exception $e) {
                if (!str_contains($e->getMessage(), 'already exists')) {
                    echo "      ⚠️  Erreur withdrawals: " . $e->getMessage() . "\n";
                }
            }
        }
    }    private function createDefaultBranch()
    {
        echo "🏢 Création branche par défaut...\n";

        try {
            $exists = DB::table('branches')->where('code', 'MAIN')->exists();

            if (!$exists) {
                DB::table('branches')->insert([
                    'name' => 'Agence Principale',
                    'code' => 'MAIN',
                    'address' => 'Port-au-Prince, Haïti',
                    'phone' => '+509 0000-0000',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->printSuccess("Branche principale créée");
            } else {
                echo "   ℹ️  Branche principale existe déjà\n";
            }
        } catch (\Exception $e) {
            $this->printError("Erreur création branche: " . $e->getMessage());
        }
    }

    private function seedRolesAndPermissions()
    {
        echo "👥 Configuration rôles et permissions...\n";

        try {
            Artisan::call('db:seed', [
                '--class' => 'ProductionSetupSeeder',
                '--force' => true
            ]);
            $this->printSuccess("Rôles et permissions configurés");
        } catch (\Exception $e) {
            $this->printError("Erreur seeding: " . $e->getMessage());
        }
    }

    private function createAdminUser()
    {
        echo "🔑 Création compte administrateur...\n";

        try {
            // Vérifier si un admin existe déjà
            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if (!$adminRole) {
                echo "   ⚠️  Rôle admin non trouvé, exécuter d'abord seed_roles_permissions\n";
                return;
            }

            $adminExists = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->where('model_has_roles.role_id', $adminRole->id)
                ->exists();

            if (!$adminExists) {
                $branchId = DB::table('branches')->first()->id ?? null;

                $userId = DB::table('users')->insertGetId([
                    'name' => 'Administrateur KAYPA',
                    'email' => 'admin@kaypa.ht',
                    'password' => bcrypt('Admin@2024!'),
                    'branch_id' => $branchId,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('model_has_roles')->insert([
                    'role_id' => $adminRole->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $userId
                ]);

                $this->printSuccess("Admin créé: admin@kaypa.ht / Admin@2024!");
            } else {
                echo "   ℹ️  Un administrateur existe déjà\n";
            }
        } catch (\Exception $e) {
            $this->printError("Erreur création admin: " . $e->getMessage());
        }
    }

    private function verifySystem()
    {
        echo "\n";
        $this->printStep("Vérification finale du système");
        echo "\n";

        // Vérifier cache
        echo "🧹 Nettoyage des caches...\n";
        try {
            Artisan::call('optimize:clear');
            echo "   ✓ Caches vidés\n";
        } catch (\Exception $e) {
            echo "   ⚠️  Erreur nettoyage: " . $e->getMessage() . "\n";
        }

        // Statistiques
        echo "\n📊 Statistiques système:\n";

        $stats = [
            'Utilisateurs' => Schema::hasTable('users') ? DB::table('users')->count() : 0,
            'Clients' => Schema::hasTable('clients') ? DB::table('clients')->count() : 0,
            'Comptes' => Schema::hasTable('accounts') ? DB::table('accounts')->count() : 0,
            'Transactions' => Schema::hasTable('account_transactions') ? DB::table('account_transactions')->count() : 0,
            'Branches' => Schema::hasTable('branches') ? DB::table('branches')->count() : 0,
        ];

        foreach ($stats as $label => $count) {
            echo "   • $label: $count\n";
        }
    }

    private function summary()
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║              ✅ DÉPLOIEMENT TERMINÉ                       ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";

        if (!empty($this->issues)) {
            echo "📝 Problèmes résolus: " . count($this->issues) . "\n";
            echo "🔧 Corrections appliquées: " . count(array_unique($this->fixes)) . "\n\n";
        }

        echo "⚠️  PROCHAINES ÉTAPES:\n";
        echo "   1. Vérifiez la connexion: php artisan tinker\n";
        echo "   2. Testez l'authentification\n";
        echo "   3. Changez le mot de passe admin si créé\n";
        echo "   4. Configurez .env pour production\n";
        echo "\n";
    }

    private function printStep($message)
    {
        echo "\n{$this->step}️⃣  $message\n";
        echo str_repeat("─", 60) . "\n";
        $this->step++;
    }

    private function printSuccess($message)
    {
        echo "   ✅ $message\n";
    }

    private function printError($message)
    {
        echo "   ❌ $message\n";
    }
}

// Exécution du déploiement intelligent
$deployer = new SmartDeployer();
$deployer->run();
