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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('montant_par_jour', 15, 2);
            $table->integer('duree'); // Durée en jours
            $table->decimal('montant_ouverture', 15, 2)->default(0);
            $table->boolean('retrait_autorise')->default(false);
            $table->integer('jour_min_retrait')->default(0);
            $table->integer('pourcentage_retrait_partiel')->default(0);
            $table->integer('frais_jour_partiel')->default(0);
            $table->integer('frais_jour_total')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_montants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->decimal('montant', 15, 2);
            $table->decimal('interet', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
