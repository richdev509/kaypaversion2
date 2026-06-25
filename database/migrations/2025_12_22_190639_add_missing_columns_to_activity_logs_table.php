<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Renommer target_type en model_type si existe
            if (Schema::hasColumn('activity_logs', 'target_type')) {
                $table->renameColumn('target_type', 'model_type');
            }
            // Renommer target_id en model_id si existe
            if (Schema::hasColumn('activity_logs', 'target_id')) {
                $table->renameColumn('target_id', 'model_id');
            }

            // Ajouter les nouvelles colonnes si elles n'existent pas
            if (!Schema::hasColumn('activity_logs', 'reason')) {
                $table->text('reason')->nullable()->after('description');
            }
            if (!Schema::hasColumn('activity_logs', 'changes')) {
                $table->json('changes')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('activity_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            // Ajouter des index pour améliorer les performances
            if (!Schema::hasColumn('activity_logs', 'action_type')) {
                $table->index('action_type');
            }
            if (!Schema::hasColumn('activity_logs', 'model_type')) {
                $table->index('model_type');
            }
            if (!Schema::hasColumn('activity_logs', 'created_at')) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées
            $table->dropColumn(['reason', 'changes', 'user_agent']);

            // Renommer les colonnes renommées
            if (Schema::hasColumn('activity_logs', 'model_type')) {
                $table->renameColumn('model_type', 'target_type');
            }
            if (Schema::hasColumn('activity_logs', 'model_id')) {
                $table->renameColumn('model_id', 'target_id');
            }
        });
    }
};
