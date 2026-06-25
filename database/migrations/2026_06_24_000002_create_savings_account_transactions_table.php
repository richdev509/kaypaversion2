<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();               // ULID
            $table->string('savings_account_number');
            $table->unsignedBigInteger('savings_account_id');        // bigint unsigned — correspond à savings_accounts.id
            $table->integer('client_id');                            // int — correspond à clients.id
            $table->enum('type', ['DEPOT', 'RETRAIT', 'FRAIS_OUVERTURE', 'INTERET']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();    // bigint unsigned — correspond à users.id
            $table->timestamps();

            $table->foreign('savings_account_id')->references('id')->on('savings_accounts')->onDelete('restrict');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_account_transactions');
    }
};
