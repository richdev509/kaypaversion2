<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Frais de service mensuel comptes courants — exécuter le 1er de chaque mois à 08h00
\Illuminate\Support\Facades\Schedule::command('cc:monthly-fees')
    ->monthlyOn(1, '08:00')
    ->withoutOverlapping()
    ->runInBackground();

// Intérêts mensuels comptes épargne — exécuter le 1er de chaque mois à 09h00
\Illuminate\Support\Facades\Schedule::command('sa:monthly-interest')
    ->monthlyOn(1, '09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Libération des blocages de solde Programme Scolaire — quotidien à 06h00
\Illuminate\Support\Facades\Schedule::command('school:release-blocks')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground();
