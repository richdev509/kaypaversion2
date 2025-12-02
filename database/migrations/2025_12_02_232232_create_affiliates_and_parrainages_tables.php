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
        // Table des affiliés (partenaires)
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone')->unique();
            $table->string('email')->unique();
            $table->string('whatsapp')->nullable();
            $table->string('code_parrain')->unique()->nullable();
            $table->enum('status', ['en_attente', 'approuve', 'rejete', 'bloque'])->default('en_attente');
            $table->string('code_verification', 4)->nullable();
            $table->boolean('email_verifie')->default(false);
            $table->timestamp('email_verifie_at')->nullable();
            $table->decimal('solde_bonus', 10, 2)->default(0);
            $table->integer('nombre_parrainages')->default(0);
            $table->text('motif_rejet')->nullable();
            $table->timestamp('approuve_at')->nullable();
            $table->unsignedBigInteger('approuve_by')->nullable();
            $table->foreign('approuve_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        // Table des parrainages (historique)
        Schema::create('parrainages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->unsignedInteger('client_id'); // INT pour correspondre à clients.id
            $table->unsignedInteger('account_id')->nullable(); // INT pour correspondre à accounts.id
            $table->string('code_utilise');
            $table->decimal('bonus_gagne', 10, 2)->default(25);
            $table->enum('status', ['en_attente', 'valide', 'paye'])->default('en_attente');
            $table->timestamp('valide_at')->nullable();
            $table->timestamp('paye_at')->nullable();
            $table->unsignedBigInteger('paye_by')->nullable();
            $table->timestamps();

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->cascadeOnDelete();
            // Pas de foreign key pour client_id et account_id car types incompatibles
            $table->foreign('paye_by')->references('id')->on('users')->nullOnDelete();
            
            $table->index('client_id');
            $table->index('account_id');
        });

        // Table des paiements aux affiliés
        Schema::create('affiliate_paiements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->decimal('montant', 10, 2);
            $table->enum('methode', ['cash', 'moncash', 'bank_transfer', 'compte_kaypa']);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('effectue_by');
            $table->timestamps();

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->cascadeOnDelete();
            $table->foreign('effectue_by')->references('id')->on('users')->cascadeOnDelete();
        });

        // Ajouter colonnes dans clients (si n'existent pas)
        if (!Schema::hasColumn('clients', 'code_parrain')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('code_parrain')->nullable()->after('email');
                $table->unsignedBigInteger('affiliate_id')->nullable()->after('code_parrain');
                // Pas de foreign key car types incompatibles
                $table->index('affiliate_id');
            });
        }

        // Ajouter colonne dans accounts (si n'existe pas)
        if (!Schema::hasColumn('accounts', 'is_from_parrainage')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->boolean('is_from_parrainage')->default(false)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les colonnes ajoutées
        if (Schema::hasColumn('accounts', 'is_from_parrainage')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn('is_from_parrainage');
            });
        }

        if (Schema::hasColumn('clients', 'code_parrain')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex(['affiliate_id']);
                $table->dropColumn(['code_parrain', 'affiliate_id']);
            });
        }

        Schema::dropIfExists('affiliate_paiements');
        Schema::dropIfExists('parrainages');
        Schema::dropIfExists('affiliates');
    }
};
