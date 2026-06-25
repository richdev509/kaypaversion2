<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, url, json, boolean, number
            $table->string('group')->nullable(); // Pour organiser les paramètres par groupe
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insérer les paramètres par défaut
        DB::table('app_settings')->insert([
            [
                'key' => 'privacy_policy_url',
                'value' => 'https://www.kaypa.com/privacy-policy',
                'type' => 'url',
                'group' => 'legal',
                'description' => 'URL de la politique de confidentialité',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'terms_of_service_url',
                'value' => 'https://www.kaypa.com/terms-of-service',
                'type' => 'url',
                'group' => 'legal',
                'description' => 'URL des conditions d\'utilisation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'support_url',
                'value' => 'https://www.kaypa.com/support',
                'type' => 'url',
                'group' => 'support',
                'description' => 'URL de la page de support',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
