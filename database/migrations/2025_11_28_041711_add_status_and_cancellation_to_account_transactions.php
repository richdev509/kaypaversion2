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
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->enum('status', ['ACTIVE', 'CANCELLED'])->default('ACTIVE')->after('note')
                ->comment('Statut de la transaction: ACTIVE (valide) ou CANCELLED (annulée)');
            $table->text('cancellation_reason')->nullable()->after('status')
                ->comment('Raison de l\'annulation si status = CANCELLED');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancellation_reason')
                ->comment('ID de l\'utilisateur qui a annulé la transaction');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by')
                ->comment('Date et heure de l\'annulation');

            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['status', 'cancellation_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
