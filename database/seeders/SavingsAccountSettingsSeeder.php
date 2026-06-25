<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class SavingsAccountSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'         => 'sce_frais_ouverture',
                'value'       => '0',
                'type'        => 'number',
                'group'       => 'compte_epargne',
                'description' => "Frais d'ouverture compte épargne (HTG) — 0 = gratuit",
            ],
            [
                'key'         => 'sce_taux_interet_mensuel',
                'value'       => '0.5',
                'type'        => 'number',
                'group'       => 'compte_epargne',
                'description' => 'Taux d\'intérêt mensuel (%) appliqué sur le solde fin de mois',
            ],
            [
                'key'         => 'sce_interet_actif',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'compte_epargne',
                'description' => 'Activer le versement automatique des intérêts mensuels',
            ],
            [
                'key'         => 'sce_solde_minimum',
                'value'       => '500',
                'type'        => 'number',
                'group'       => 'compte_epargne',
                'description' => 'Solde minimum obligatoire (GDS) — dépôt initial et plancher de retrait',
            ],
            [
                'key'         => 'sce_solde_minimum_interet',
                'value'       => '500',
                'type'        => 'number',
                'group'       => 'compte_epargne',
                'description' => 'Solde minimum (GDS) requis pour bénéficier des intérêts',
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
