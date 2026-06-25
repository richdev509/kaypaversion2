<?php

namespace App\Console\Commands;

use App\Models\BalancePaiementOnline;
use App\Models\TransactionOnline;
use Illuminate\Console\Command;

class RecalculateOnlineBalance extends Command
{
    protected $signature = 'balance:recalculate-online';
    protected $description = 'Recalculer le solde des paiements en ligne à partir des transactions';

    public function handle()
    {
        $this->info('Recalcul du solde des paiements en ligne...');

        // Calculer les totaux à partir des transactions réussies
        $totalDepot = TransactionOnline::where('type', 'depot')
            ->where('statut', 'reussie')
            ->sum('montant');

        $totalRetrait = TransactionOnline::where('type', 'retrait')
            ->where('statut', 'reussie')
            ->sum('montant');

        $totalOuverture = TransactionOnline::where('type', 'ouverture')
            ->where('statut', 'reussie')
            ->sum('montant');

        $balance = $totalDepot - $totalRetrait + $totalOuverture;

        $nombreTransactions = TransactionOnline::where('statut', 'reussie')->count();

        $derniereTransaction = TransactionOnline::where('statut', 'reussie')
            ->latest('created_at')
            ->first();

        // Mettre à jour la balance
        $balanceRecord = BalancePaiementOnline::getSolde();
        $balanceRecord->balance = $balance;
        $balanceRecord->total_depot = $totalDepot;
        $balanceRecord->total_retrait = $totalRetrait;
        $balanceRecord->total_ouverture = $totalOuverture;
        $balanceRecord->nombre_transactions = $nombreTransactions;
        $balanceRecord->derniere_transaction = $derniereTransaction?->created_at;
        $balanceRecord->save();

        $this->info('✅ Recalcul terminé avec succès!');
        $this->newLine();
        $this->table(
            ['Indicateur', 'Valeur'],
            [
                ['Balance', number_format($balance, 2) . ' HTG'],
                ['Total Dépôts', number_format($totalDepot, 2) . ' HTG'],
                ['Total Retraits', number_format($totalRetrait, 2) . ' HTG'],
                ['Total Ouverture', number_format($totalOuverture, 2) . ' HTG'],
                ['Nombre de transactions', $nombreTransactions],
                ['Dernière transaction', $derniereTransaction?->created_at->format('d/m/Y H:i:s') ?? 'N/A'],
            ]
        );

        return Command::SUCCESS;
    }
}
