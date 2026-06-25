<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_accounts', function (Blueprint $table) {
            $table->id();                                              // bigint unsigned
            $table->string('account_number')->unique();               // KCE-XXXXXXXXXX
            $table->integer('client_id');                             // int — correspond à clients.id
            $table->integer('branch_id')->nullable();                 // int — correspond à branches.id
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('status', ['actif', 'suspendu', 'cloture'])->default('actif');
            $table->timestamp('last_interest_at')->nullable();        // date du dernier versement d'intérêt
            $table->unsignedBigInteger('created_by')->nullable();     // bigint unsigned — correspond à users.id
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_accounts');
    }
};
