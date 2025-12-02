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
        Schema::create('fund_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // FMV-YYYYMMDD-XXXX
            $table->enum('type', ['IN', 'OUT']); // Entrée ou sortie
            $table->decimal('amount', 15, 2);

            // Source et destination (sans foreign key car branches existe déjà sans migration)
            $table->unsignedInteger('source_branch_id')->nullable();
            $table->unsignedInteger('destination_branch_id')->nullable();

            // Type de source/destination
            $table->enum('source_type', ['SUCCURSALE', 'BANQUE', 'EXTERNE', 'INITIAL'])->default('SUCCURSALE');
            $table->string('external_source')->nullable(); // Si EXTERNE ou BANQUE

            // Détails
            $table->text('reason'); // Raison du mouvement
            $table->text('notes')->nullable();

            // Statut et validation
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Index
            $table->index(['status', 'created_at']);
            $table->index('source_branch_id');
            $table->index('destination_branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_movements');
    }
};
