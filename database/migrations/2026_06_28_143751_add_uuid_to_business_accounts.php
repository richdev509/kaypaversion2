<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'business_current_accounts',
            'business_savings_accounts',
            'business_credit_limits',
            'business_credit_alerts',
        ];

        foreach ($tables as $table) {
            // Ajouter la colonne nullable d'abord
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('uuid')->nullable()->unique()->after('id');
            });

            // Remplir les lignes existantes
            DB::table($table)->orderBy('id')->each(function ($row) use ($table) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            });

            // Rendre NOT NULL maintenant que toutes les lignes ont un UUID
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('uuid')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'business_current_accounts',
            'business_savings_accounts',
            'business_credit_limits',
            'business_credit_alerts',
        ] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('uuid'));
        }
    }
};
