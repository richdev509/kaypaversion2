<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Élargir la colonne type pour accepter les nouveaux types
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->string('type', 30)->change();
        });

        // Mettre à jour les types d'ajustements existants dans la base de données
        DB::statement("UPDATE account_transactions
                       SET type = 'AJUSTEMENT-DEPOT'
                       WHERE type = 'AJUSTEMENT'
                       AND (note LIKE '%INCREASE%' OR note LIKE '%AUGMENTATION%')");

        DB::statement("UPDATE account_transactions
                       SET type = 'AJUSTEMENT-RETRAIT'
                       WHERE type = 'AJUSTEMENT'
                       AND (note LIKE '%DECREASE%' OR note LIKE '%DIMINUTION%')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien type unique
        DB::statement("UPDATE account_transactions
                       SET type = 'AJUSTEMENT'
                       WHERE type IN ('AJUSTEMENT-DEPOT', 'AJUSTEMENT-RETRAIT')");
    }
};
