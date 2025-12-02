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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaction')->unique();
            $table->string('account_id');
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['PAIEMENT', 'RETRAIT', 'AJUSTEMENT', 'Paiement initial']);
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_after', 15, 2);
            $table->enum('mode', ['partiel', 'total'])->nullable();
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('account_id');
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
