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
        Schema::table('transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('modified_by')->nullable()->after('note');
            $table->timestamp('modified_at')->nullable()->after('modified_by');
            $table->text('modification_history')->nullable()->after('modified_at');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('modification_history');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');

            $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropForeign(['modified_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'modified_by',
                'modified_at',
                'modification_history',
                'cancelled_by',
                'cancelled_at',
                'cancellation_reason'
            ]);
        });
    }
};
