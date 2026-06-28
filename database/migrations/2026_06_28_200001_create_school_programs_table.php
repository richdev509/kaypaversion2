<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('inscription_debut');
            $table->date('inscription_fin');
            $table->decimal('solde_minimum_epargne', 15, 2)->default(2000.00);
            $table->decimal('montant_blocage', 15, 2)->default(2000.00);
            $table->unsignedInteger('duree_blocage_jours')->default(90);
            $table->decimal('tier1_seuil', 15, 2)->default(2000.00);
            $table->decimal('tier1_coupon', 15, 2)->default(500.00);
            $table->decimal('tier2_seuil', 15, 2)->default(10000.00);
            $table->decimal('tier2_coupon', 15, 2)->default(1000.00);
            $table->enum('status', ['actif', 'archive'])->default('actif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_programs');
    }
};
