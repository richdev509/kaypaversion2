<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;
use App\Models\AccountTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecalculateBranchCashBalance extends Command
{
    protected $signature = 'branch:recalculate-cash';
    protected $description = 'Recalculer le cash_balance de toutes les branches basé sur les transactions';

    public function handle()
    {
        $this->info('🔄 Recalcul des soldes caisse pour toutes les branches...');

        $branches = Branch::all();

        foreach ($branches as $branch) {
            // Réinitialiser le solde
            $initialBalance = 0;

            // Récupérer tous les utilisateurs de cette branche
            $userIds = User::where('branch_id', $branch->id)->pluck('id');

            if ($userIds->isEmpty()) {
                $this->warn("⚠️  Branch {$branch->name}: Aucun utilisateur assigné");
                continue;
            }

            // Calculer le total des PAIEMENTS (entrées +)
            $totalPayments = DB::table('account_transactions')
                ->where('type', 'PAIEMENT')
                ->whereIn('created_by', $userIds)
                ->sum('amount');

            // Calculer le total des RETRAITS (sorties -)
            $totalWithdrawals = DB::table('account_transactions')
                ->where('type', 'RETRAIT')
                ->whereIn('created_by', $userIds)
                ->sum('amount');

            // Calculer le solde net
            $netBalance = $initialBalance + $totalPayments - $totalWithdrawals;

            // Mettre à jour la branche
            $branch->update(['cash_balance' => $netBalance]);

            $this->info("✅ {$branch->name}: {$netBalance} HTG (Paiements: +{$totalPayments}, Retraits: -{$totalWithdrawals})");
        }

        $this->info('');
        $this->info('✅ Recalcul terminé!');

        return 0;
    }
}
