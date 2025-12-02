<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branches
        $now = now();
        $branches = [
            ['id' => 1, 'name' => 'Saga Center', 'address' => 'Saga Center, Pétion-Ville', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Delmas', 'address' => 'Delmas 33, Port-au-Prince', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'République Dominicaine', 'address' => 'Santo Domingo, RD', 'created_at' => $now, 'updated_at' => $now],
        ];
        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(['id' => $branch['id']], $branch);
        }

        // 2. Départements
        $departments = [
            ['id' => 1, 'name' => 'Ouest'],
            ['id' => 2, 'name' => 'Artibonite'],
            ['id' => 3, 'name' => 'Nord'],
        ];
        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(['id' => $dept['id']], $dept);
        }

        // 3. Communes
        $communes = [
            ['id' => 1, 'name' => 'Port-au-Prince', 'department_id' => 1],
            ['id' => 2, 'name' => 'Pétion-Ville', 'department_id' => 1],
            ['id' => 3, 'name' => 'Delmas', 'department_id' => 1],
        ];
        foreach ($communes as $commune) {
            DB::table('communes')->updateOrInsert(['id' => $commune['id']], $commune);
        }

        // 4. Villes
        $cities = [
            ['id' => 1, 'name' => 'Delmas 33', 'commune_id' => 3],
            ['id' => 2, 'name' => 'Pétion-Ville Center', 'commune_id' => 2],
            ['id' => 3, 'name' => 'Port-au-Prince Centre', 'commune_id' => 1],
        ];
        foreach ($cities as $city) {
            DB::table('cities')->updateOrInsert(['id' => $city['id']], $city);
        }

        // 5. Plans
        $plans = [
            ['id' => 1, 'name' => 'Plan 30 Jours', 'montant_par_jour' => 100.00, 'duree' => 30, 'montant_ouverture' => 3000.00, 'retrait_autorise' => true, 'jour_min_retrait' => 15, 'pourcentage_retrait_partiel' => 50, 'frais_jour_partiel' => 3, 'frais_jour_total' => 5],
            ['id' => 2, 'name' => 'Plan 60 Jours', 'montant_par_jour' => 100.00, 'duree' => 60, 'montant_ouverture' => 6000.00, 'retrait_autorise' => true, 'jour_min_retrait' => 30, 'pourcentage_retrait_partiel' => 50, 'frais_jour_partiel' => 5, 'frais_jour_total' => 10],
            ['id' => 3, 'name' => 'Plan 90 Jours', 'montant_par_jour' => 100.00, 'duree' => 90, 'montant_ouverture' => 9000.00, 'retrait_autorise' => true, 'jour_min_retrait' => 45, 'pourcentage_retrait_partiel' => 50, 'frais_jour_partiel' => 7, 'frais_jour_total' => 15],
        ];
        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(['id' => $plan['id']], $plan);
        }

        // 6. Montants pour chaque plan (plan_montants)
        $planMontants = [
            // Plan 30 Jours - Montants de 3000 à 10000 HTG
            ['plan_id' => 1, 'montant' => 3000.00, 'interet' => 150.00],
            ['plan_id' => 1, 'montant' => 5000.00, 'interet' => 250.00],
            ['plan_id' => 1, 'montant' => 7000.00, 'interet' => 350.00],
            ['plan_id' => 1, 'montant' => 10000.00, 'interet' => 500.00],

            // Plan 60 Jours - Montants de 6000 à 20000 HTG
            ['plan_id' => 2, 'montant' => 6000.00, 'interet' => 360.00],
            ['plan_id' => 2, 'montant' => 10000.00, 'interet' => 600.00],
            ['plan_id' => 2, 'montant' => 15000.00, 'interet' => 900.00],
            ['plan_id' => 2, 'montant' => 20000.00, 'interet' => 1200.00],

            // Plan 90 Jours - Montants de 9000 à 30000 HTG
            ['plan_id' => 3, 'montant' => 9000.00, 'interet' => 630.00],
            ['plan_id' => 3, 'montant' => 15000.00, 'interet' => 1050.00],
            ['plan_id' => 3, 'montant' => 20000.00, 'interet' => 1400.00],
            ['plan_id' => 3, 'montant' => 30000.00, 'interet' => 2100.00],
        ];

        foreach ($planMontants as $montant) {
            DB::table('plan_montants')->updateOrInsert(
                ['plan_id' => $montant['plan_id'], 'montant' => $montant['montant']],
                $montant
            );
        }

        echo "\n✅ 3 Branches | 3 Départements | 3 Communes | 3 Villes | 3 Plans | 12 Montants\n\n";
    }
}
