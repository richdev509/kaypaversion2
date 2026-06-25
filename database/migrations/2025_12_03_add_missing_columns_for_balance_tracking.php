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
        // Ajouter cash_balance à la table branches
        if (!Schema::hasColumn('branches', 'cash_balance')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->decimal('cash_balance', 15, 2)->default(0)->after('name')
                    ->comment('Solde de caisse de la succursale');
            });
        }

        // Ajouter les colonnes d'annulation à account_transactions
        Schema::table('account_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('account_transactions', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()
                    ->comment('Raison de l\'annulation de la transaction');
            }

            if (!Schema::hasColumn('account_transactions', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()
                    ->comment('ID de l\'utilisateur qui a annulé la transaction');
                $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('account_transactions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()
                    ->comment('Date et heure de l\'annulation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les colonnes d'annulation
        Schema::table('account_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('account_transactions', 'cancelled_by')) {
                $table->dropForeign(['cancelled_by']);
                $table->dropColumn('cancelled_by');
            }
            if (Schema::hasColumn('account_transactions', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('account_transactions', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
        });

        // Supprimer cash_balance
        if (Schema::hasColumn('branches', 'cash_balance')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('cash_balance');
            });
        }
    }
};
