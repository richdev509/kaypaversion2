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
            $table->boolean('is_disputed')->default(false)->after('status');
            $table->enum('dispute_status', ['pending', 'investigating', 'resolved', 'rejected'])->nullable()->after('is_disputed');
            $table->text('dispute_reason')->nullable()->after('dispute_status');
            $table->unsignedBigInteger('disputed_by')->nullable()->after('dispute_reason');
            $table->timestamp('disputed_at')->nullable()->after('disputed_by');
            $table->text('dispute_resolution')->nullable()->after('disputed_at');
            $table->unsignedBigInteger('resolved_by')->nullable()->after('dispute_resolution');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');

            $table->foreign('disputed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropForeign(['disputed_by']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn([
                'is_disputed',
                'dispute_status',
                'dispute_reason',
                'disputed_by',
                'disputed_at',
                'dispute_resolution',
                'resolved_by',
                'resolved_at'
            ]);
        });
    }
};
