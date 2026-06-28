<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_program_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_program_id')->constrained('school_programs')->restrictOnDelete();
            $table->integer('client_id');
            $table->foreignId('savings_account_id')->constrained('savings_accounts')->restrictOnDelete();
            $table->string('coupon_code', 20)->unique();
            $table->decimal('coupon_value', 15, 2);
            $table->tinyInteger('tier')->unsigned();
            $table->enum('coupon_status', ['active', 'used', 'expired', 'cancelled'])->default('active');
            $table->decimal('balance_blocked', 15, 2)->default(0);
            $table->timestamp('blocked_until')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_by_affiliate_id')->nullable()->constrained('affiliates')->nullOnDelete();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_program_id', 'client_id']);
            $table->index('coupon_code');
            $table->index('coupon_status');
            $table->index('blocked_until');
            $table->index('savings_account_id');

            $table->foreign('client_id')->references('id')->on('clients')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_program_enrollments');
    }
};
