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
        Schema::create('transfer_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 15, 2)->default(500); // Montant minimal: 500 GDS
            $table->decimal('max_amount', 15, 2)->default(75000); // Montant maximal: 75,000 GDS
            $table->decimal('transfer_fee_percentage', 5, 2)->default(2); // Frais en % (ex: 2%)
            $table->decimal('transfer_fee_fixed', 15, 2)->default(0); // Frais fixe (optionnel)
            $table->decimal('kaypa_client_discount', 5, 2)->default(10); // Réduction client Kaypa: 10%
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        DB::table('transfer_settings')->insert([
            'min_amount' => 500,
            'max_amount' => 75000,
            'transfer_fee_percentage' => 0,
            'transfer_fee_fixed' => 100,
            'kaypa_client_discount' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_settings');
    }
};
