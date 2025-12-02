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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['deposit', 'withdrawal', 'all'])->default('all');
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('branch_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('total_count')->default(0);
            $table->json('data')->nullable(); // Détails du rapport
            $table->timestamps();

            $table->index(['type', 'period_type', 'start_date', 'end_date']);
            $table->index('branch_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
