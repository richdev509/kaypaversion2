<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BalancePaiementOnline;
use App\Models\TransactionOnline;

echo "=== BALANCE PAIEMENT ONLINE ===\n";
$balance = BalancePaiementOnline::getSolde();
echo "Balance actuelle: " . number_format($balance->balance, 2) . " HTG\n";
echo "Total dépôts: " . number_format($balance->total_depot, 2) . " HTG\n";
echo "Total retraits: " . number_format($balance->total_retrait, 2) . " HTG\n";
echo "Total ouverture: " . number_format($balance->total_ouverture, 2) . " HTG\n";
echo "Nombre transactions: " . $balance->nombre_transactions . "\n";
echo "Dernière transaction: " . ($balance->derniere_transaction ? $balance->derniere_transaction->format('d/m/Y H:i') : 'N/A') . "\n\n";

echo "=== TRANSACTIONS PAR TYPE ET STATUT ===\n";
$stats = TransactionOnline::selectRaw('type, statut, COUNT(*) as count, SUM(montant) as total')
    ->groupBy('type', 'statut')
    ->orderBy('type')
    ->orderBy('statut')
    ->get();

foreach ($stats as $stat) {
    echo $stat->type . " - " . $stat->statut . ": " . $stat->count . " transactions, Total: " . number_format($stat->total, 2) . " HTG\n";
}

echo "\n=== CALCUL CORRECT DES TOTAUX ===\n";
$totalDepot = TransactionOnline::where('type', 'depot')
    ->where('statut', 'reussie')
    ->sum('montant');
$totalRetrait = TransactionOnline::where('type', 'retrait')
    ->where('statut', 'reussie')
    ->sum('montant');
$totalOuverture = TransactionOnline::where('type', 'ouverture')
    ->where('statut', 'reussie')
    ->sum('montant');

echo "Total dépôts réussis: " . number_format($totalDepot, 2) . " HTG\n";
echo "Total retraits réussis: " . number_format($totalRetrait, 2) . " HTG\n";
echo "Total ouverture réussis: " . number_format($totalOuverture, 2) . " HTG\n";
echo "Balance calculée: " . number_format($totalDepot - $totalRetrait + $totalOuverture, 2) . " HTG\n";
