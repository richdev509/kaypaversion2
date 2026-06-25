<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class CurrentAccountSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'         => 'cc_frais_ouverture',
                'value'       => '200',
                'type'        => 'number',
                'group'       => 'compte_courant',
                'description' => 'Frais d\'ouverture compte courant (GDS)',
            ],
            [
                'key'         => 'cc_frais_service_mensuel',
                'value'       => '10',
                'type'        => 'number',
                'group'       => 'compte_courant',
                'description' => 'Frais de service mensuel compte courant (HTG)',
            ],
            [
                'key'         => 'cc_frais_service_actif',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'compte_courant',
                'description' => 'Activer les frais de service mensuel',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
