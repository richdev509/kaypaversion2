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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action_type', 50); // create, update, delete, login, logout, etc.
            $table->string('model_type')->nullable(); // Transfer, Account, Client, etc.
            $table->unsignedBigInteger('model_id')->nullable(); // ID du modèle concerné
            $table->text('description'); // Description détaillée de l'action
            $table->text('reason')->nullable(); // Raison de l'action si applicable
            $table->json('changes')->nullable(); // Changements effectués (avant/après)
            $table->string('ip_address', 45)->nullable(); // Support IPv4 et IPv6
            $table->text('user_agent')->nullable(); // Navigateur/appareil
            $table->timestamps();

            // Index pour améliorer les performances de recherche
            $table->index('user_id');
            $table->index('action_type');
            $table->index('model_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
