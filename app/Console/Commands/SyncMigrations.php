<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncMigrations extends Command
{
    protected $signature = 'migrate:sync';

    protected $description = 'Synchronise la table migrations avec les tables existantes dans la base de données';

    public function handle()
    {
        $this->info('🔄 Synchronisation des migrations avec les tables existantes...');
        $this->newLine();

        // Vérifier que la table migrations existe
        if (!Schema::hasTable('migrations')) {
            $this->error('❌ La table migrations n\'existe pas!');
            return 1;
        }

        // Obtenir toutes les tables de la base de données
        $existingTables = $this->getExistingTables();
        $this->info('📊 Tables existantes: ' . count($existingTables));

        // Obtenir les migrations déjà enregistrées
        $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
        $this->info('📋 Migrations enregistrées: ' . count($ranMigrations));
        $this->newLine();

        // Obtenir tous les fichiers de migration
        $allMigrations = $this->getAllMigrationFiles();

        // Mapper les migrations aux tables qu'elles créent
        $migrationToTable = $this->mapMigrationsToTables($allMigrations);

        $synced = 0;
        $skipped = 0;

        foreach ($migrationToTable as $migration => $table) {
            // Si la migration n'est pas enregistrée mais que la table existe
            if (!in_array($migration, $ranMigrations) && in_array($table, $existingTables)) {
                try {
                    DB::table('migrations')->insert([
                        'migration' => $migration,
                        'batch' => 1
                    ]);
                    $this->line("   ✓ Migration synchronisée: $migration → $table");
                    $synced++;
                } catch (\Exception $e) {
                    $this->warn("   ⚠️  Erreur pour $migration: " . $e->getMessage());
                }
            } elseif (in_array($migration, $ranMigrations)) {
                $skipped++;
            }
        }

        $this->newLine();

        if ($synced > 0) {
            $this->info("✅ $synced migration(s) synchronisée(s)");
        }

        if ($skipped > 0) {
            $this->line("ℹ️  $skipped migration(s) déjà enregistrée(s)");
        }

        if ($synced === 0 && $skipped === 0) {
            $this->info('✅ Aucune synchronisation nécessaire');
        }

        return 0;
    }

    private function getExistingTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . config('database.connections.mysql.database');

        return array_map(function($table) use ($dbName) {
            return $table->$dbName;
        }, $tables);
    }

    private function getAllMigrationFiles(): array
    {
        $path = database_path('migrations');
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
            // Format alternatif: YYYY_MM_DD_HHMMSS_add_column_to_table_table.php
            elseif (preg_match('/to_(.+?)_table/', $migration, $matches)) {
                $tableName = $matches[1];
                $map[$migration] = $tableName;
            }
        }

        return $map;
    }
}
