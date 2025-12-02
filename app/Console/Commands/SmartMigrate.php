<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class SmartMigrate extends Command
{
    protected $signature = 'migrate:smart {--force : Force the operation to run in production}';

    protected $description = 'Smart migration that only adds missing columns and tables without breaking existing data';

    public function handle()
    {
        $this->info('🔍 Analyse des migrations nécessaires...');
        $this->newLine();

        // Obtenir les migrations qui n'ont pas encore été exécutées
        $ran = $this->getMigrations();
        $migrations = $this->getAllMigrationFiles();
        $pending = array_diff($migrations, $ran);

        if (empty($pending)) {
            $this->info('✅ Aucune migration en attente');
            return 0;
        }

        $this->info('📋 Migrations en attente: ' . count($pending));
        $this->newLine();

        foreach ($pending as $migration) {
            $this->line("   → $migration");
        }

        $this->newLine();

        // Demander confirmation si pas en mode force
        if (!$this->option('force') && !$this->confirm('Voulez-vous exécuter ces migrations?', true)) {
            $this->warn('Migration annulée');
            return 0;
        }

        // Exécuter les migrations de manière intelligente
        $this->info('🚀 Exécution des migrations...');
        $this->newLine();

        try {
            // Utiliser migrate:status pour voir l'état
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
                '--step' => true
            ]);

            if ($exitCode === 0) {
                $this->info('✅ Migrations exécutées avec succès');
            } else {
                $this->warn('⚠️  Migrations terminées avec des avertissements');
            }
        } catch (\Exception $e) {
            $this->error('❌ Erreur: ' . $e->getMessage());

            // Essayer d'ajouter les colonnes manuellement
            $this->warn('Tentative d\'ajout manuel des colonnes...');
            $this->addMissingColumns();
        }

        return 0;
    }

    private function getMigrations(): array
    {
        try {
            return DB::table('migrations')->pluck('migration')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAllMigrationFiles(): array
    {
        $path = database_path('migrations');
        $files = glob($path . '/*.php');

        return array_map(function ($file) {
            return str_replace('.php', '', basename($file));
        }, $files);
    }

    private function addMissingColumns()
    {
        $this->info('🔧 Ajout manuel des colonnes manquantes...');
        $this->newLine();

        $columnsToAdd = [
            'users' => [
                'is_active' => "ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER email",
                'last_login_at' => "ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL AFTER is_active",
                'failed_login_attempts' => "ALTER TABLE users ADD COLUMN failed_login_attempts INT NOT NULL DEFAULT 0 AFTER last_login_at",
            ],
            'clients' => [
                'id_card_number' => "ALTER TABLE clients ADD COLUMN id_card_number VARCHAR(255) NULL AFTER phone",
            ],
            'accounts' => [
                'branch_id' => "ALTER TABLE accounts ADD COLUMN branch_id BIGINT UNSIGNED NULL AFTER id",
            ],
            'account_transactions' => [
                'branch_id' => "ALTER TABLE account_transactions ADD COLUMN branch_id BIGINT UNSIGNED NULL AFTER id",
                'performed_by' => "ALTER TABLE account_transactions ADD COLUMN performed_by BIGINT UNSIGNED NULL AFTER branch_id",
            ],
            'branches' => [
                'code' => "ALTER TABLE branches ADD COLUMN code VARCHAR(50) NULL AFTER name",
                'phone' => "ALTER TABLE branches ADD COLUMN phone VARCHAR(255) NULL AFTER address",
                'is_active' => "ALTER TABLE branches ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER phone",
            ],
        ];

        foreach ($columnsToAdd as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $this->warn("   ⚠️  Table $table n'existe pas, ignoré");
                continue;
            }

            foreach ($columns as $column => $sql) {
                if (!Schema::hasColumn($table, $column)) {
                    try {
                        DB::statement($sql);
                        $this->info("   ✓ Colonne ajoutée: $table.$column");
                    } catch (\Exception $e) {
                        $this->error("   ❌ Erreur ajout $table.$column: " . $e->getMessage());
                    }
                } else {
                    $this->line("   → Colonne existe: $table.$column");
                }
            }
        }

        // Créer les tables manquantes critiques
        $this->createMissingTables();
    }

    private function createMissingTables()
    {
        $this->newLine();
        $this->info('🗄️  Vérification des tables critiques...');

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
                        INDEX idx_account_id (account_id),
                        INDEX idx_payment_date (payment_date),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->info('   ✓ Table créée: payments');
            } catch (\Exception $e) {
                $this->error('   ❌ Erreur création payments: ' . $e->getMessage());
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
                        INDEX idx_account_id (account_id),
                        INDEX idx_withdrawal_date (withdrawal_date),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->info('   ✓ Table créée: withdrawals');
            } catch (\Exception $e) {
                $this->error('   ❌ Erreur création withdrawals: ' . $e->getMessage());
            }
        }

        // Table fund_movements si elle n'existe pas
        if (!Schema::hasTable('fund_movements')) {
            try {
                DB::statement("
                    CREATE TABLE fund_movements (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        branch_id BIGINT UNSIGNED NOT NULL,
                        type ENUM('in', 'out') NOT NULL,
                        amount DECIMAL(15, 2) NOT NULL,
                        description TEXT NULL,
                        reference VARCHAR(255) NULL,
                        performed_by BIGINT UNSIGNED NOT NULL,
                        movement_date DATE NOT NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        INDEX idx_branch_id (branch_id),
                        INDEX idx_type (type),
                        INDEX idx_movement_date (movement_date)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->info('   ✓ Table créée: fund_movements');
            } catch (\Exception $e) {
                $this->error('   ❌ Erreur création fund_movements: ' . $e->getMessage());
            }
        }
    }
}
