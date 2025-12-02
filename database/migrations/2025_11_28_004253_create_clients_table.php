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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('kaypa_identity_id')->unique()->nullable();
            $table->string('client_id')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
            $table->string('nationalite')->default('Haitienne');
            $table->date('birth_date')->nullable();
            $table->string('status_kyc')->default('pending');
            $table->string('card_number')->nullable();
            $table->string('numero_carte')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('branch_id')->nullable();
            $table->date('date_emission')->nullable();
            $table->date('date_expiration')->nullable();
            $table->string('document_id_type')->nullable();
            $table->string('document_id_number')->nullable();
            $table->string('front_id_path')->nullable();
            $table->string('back_id_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('id_nif_cin')->nullable();
            $table->string('id_nif_cin_file_path')->nullable();
            $table->boolean('kyc')->default(false);
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('commune_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('profil_path')->nullable();
            $table->text('address')->nullable();
            $table->string('password')->nullable();
            $table->boolean('password_reset')->default(false);
            $table->string('area_code')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('restrict');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('commune_id')->references('id')->on('communes')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
