<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\SavingsAccount;
use App\Services\SavingsAccountService;
use Illuminate\Console\Command;

class ApplySavingsAccountMonthlyInterest extends Command
{
    protected $signature   = 'sa:monthly-interest';
    protected $description = 'Appliquer les intérêts mensuels sur tous les comptes épargne actifs';

    public function handle(SavingsAccountService $service): int
    {
        if (AppSetting::get('sce_interet_actif', 'true') !== 'true') {
            $this->info('Intérêts désactivés — aucune opération effectuée.');
            return 0;
        }

        $accounts = SavingsAccount::where('status', 'actif')->get();
        $applied  = 0;
        $skipped  = 0;

        foreach ($accounts as $account) {
            $tx = $service->applyMonthlyInterest($account);
            $tx ? $applied++ : $skipped++;
        }

        $this->info("Intérêts appliqués : {$applied} compte(s). Ignorés (solde insuffisant / déjà prélevé) : {$skipped}.");

        return 0;
    }
}
