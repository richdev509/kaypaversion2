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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique(); // Numéro de transfert unique

            // Informations expéditeur
            $table->string('sender_name');
            $table->string('sender_phone'); // +509 xx-xx-xxxx
            $table->string('sender_ninu', 10); // 10 chiffres
            $table->text('sender_address')->nullable();
            $table->unsignedBigInteger('sender_department_id')->nullable();
            $table->unsignedBigInteger('sender_commune_id')->nullable();
            $table->unsignedBigInteger('sender_city_id')->nullable();
            $table->string('sender_account_id')->nullable(); // Si client Kaypa (account_id est VARCHAR)

            // Informations bénéficiaire
            $table->string('receiver_name');
            $table->string('receiver_phone'); // +509 xx-xx-xxxx

            // Montant et frais
            $table->decimal('amount', 15, 2); // Montant à transférer
            $table->decimal('fees', 15, 2); // Frais de transfert
            $table->decimal('discount', 15, 2)->default(0); // Réduction (10% si client Kaypa)
            $table->decimal('total_amount', 15, 2); // Montant total payé (amount + fees - discount)

            // Statut et traçabilité
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('created_by'); // Agent qui crée le transfert
            $table->unsignedInteger('branch_id'); // Agence d'envoi

            // Réception
            $table->unsignedBigInteger('paid_by')->nullable(); // Agent qui paie
            $table->unsignedInteger('paid_at_branch_id')->nullable(); // Agence de paiement
            $table->timestamp('paid_at')->nullable();
            $table->string('receiver_ninu', 10)->nullable(); // NINU du bénéficiaire (vérifié à la réception)
            $table->text('receiver_address')->nullable(); // Adresse du bénéficiaire (optionnel)
            $table->unsignedBigInteger('receiver_department_id')->nullable();
            $table->unsignedBigInteger('receiver_commune_id')->nullable();
            $table->unsignedBigInteger('receiver_city_id')->nullable();

            $table->text('note')->nullable();
            $table->timestamps();

            // Index
            $table->index('transfer_number');
            $table->index('status');
            $table->index('sender_phone');
            $table->index('receiver_phone');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
