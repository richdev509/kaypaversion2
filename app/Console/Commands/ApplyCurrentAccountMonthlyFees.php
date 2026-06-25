<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\CurrentAccount;
use App\Services\CurrentAccountService;
use Illuminate\Console\Command;

class ApplyCurrentAccountMonthlyFees extends Command
{
    protected $signature   = 'cc:monthly-fees';
    protected $description = 'Prélève les frais de service mensuel sur tous les comptes courants actifs';

    public function handle(CurrentAccountService $service): int
    {
        $active = AppSetting::get('cc_frais_service_actif', true);

        if (!$active) {
            $this->info('Frais de service mensuel désactivés — aucun prélèvement.');
            return self::SUCCESS;
        }

        $accounts = CurrentAccount::active()->get();
        $count    = 0;

        foreach ($accounts as $account) {
            $tx = $service->applyMonthlyFee($account);
            if ($tx) {
                $count++;
                $this->line("  ✓ {$account->account_number} — {$tx->amount} HTG prélevé");
            }
        }

        $this->info("Frais mensuels appliqués : {$count} compte(s) traité(s).");
        return self::SUCCESS;
    }
}
