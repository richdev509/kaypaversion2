<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalancePaiementOnline extends Model
{
    protected $table = 'balance_paiement_online';

    protected $fillable = [
        'balance',
        'total_depot',
        'total_retrait',
        'total_ouverture',
        'nombre_transactions',
        'derniere_transaction',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_depot' => 'decimal:2',
        'total_retrait' => 'decimal:2',
        'total_ouverture' => 'decimal:2',
        'nombre_transactions' => 'integer',
        'derniere_transaction' => 'datetime',
    ];

    /**
     * Obtenir l'instance unique du solde (singleton)
     */
    public static function getSolde()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'balance' => 0,
                'total_depot' => 0,
                'total_retrait' => 0,
                'total_ouverture' => 0,
                'nombre_transactions' => 0,
            ]
        );
    }

    /**
     * Mettre à jour le solde après une transaction
     */
    public function updateAfterTransaction($type, $montant)
    {
        switch ($type) {
            case 'depot':
                $this->balance += $montant;
                $this->total_depot += $montant;
                break;

            case 'retrait':
                $this->balance -= $montant;
                $this->total_retrait += $montant;
                break;

            case 'ouverture':
                $this->balance += $montant;
                $this->total_ouverture += $montant;
                break;
        }

        $this->nombre_transactions++;
        $this->derniere_transaction = now();
        $this->save();
    }

    /**
     * Obtenir les transactions en ligne
     */
    public function transactions()
    {
        return TransactionOnline::orderBy('created_at', 'desc')->get();
    }
}
