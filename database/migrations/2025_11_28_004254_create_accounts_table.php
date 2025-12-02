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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_id')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            $table->decimal('montant_journalier', 15, 2);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('status', ['actif', 'inactif', 'cloture', 'pending'])->default('pending');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('amount_after', 15, 2)->default(0);
            $table->decimal('montant_dispo_retrait', 15, 2)->default(0);
            $table->decimal('withdraw', 15, 2)->default(0);
            $table->boolean('retrait_status')->default(false);
            $table->decimal('credit_locked', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
