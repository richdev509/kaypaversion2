<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionOnline extends Model
{
    protected $table = 'transaction_online';

    protected $fillable = [
        'account_id',
        'type',
        'montant',
        'balance_avant',
        'balance_apres',
        'ordre_id',
        'gateway',
        'statut',
        'description',
        'metadata',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'balance_avant' => 'decimal:2',
        'balance_apres' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le compte
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    /**
     * Scopes pour filtrer par type
     */
    public function scopeOuverture($query)
    {
        return $query->where('type', 'ouverture');
    }

    public function scopeDepot($query)
    {
        return $query->where('type', 'depot');
    }

    public function scopeRetrait($query)
    {
        return $query->where('type', 'retrait');
    }

    /**
     * Scopes pour filtrer par statut
     */
    public function scopeReussie($query)
    {
        return $query->where('statut', 'reussie');
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopeEchouee($query)
    {
        return $query->where('statut', 'echouee');
    }

    /**
     * Obtenir la couleur du badge selon le type
     */
    public function getTypeBadgeColorAttribute()
    {
        return match($this->type) {
            'depot' => 'blue',
            'retrait' => 'red',
            'ouverture' => 'green',
            default => 'gray',
        };
    }

    /**
     * Obtenir la couleur du badge selon le statut
     */
    public function getStatutBadgeColorAttribute()
    {
        return match($this->statut) {
            'reussie' => 'green',
            'en_cours' => 'yellow',
            'echouee' => 'red',
            'annulee' => 'gray',
            'initialiser' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Obtenir le libellé du type
     */
    public function getTypeLibelleAttribute()
    {
        return match($this->type) {
            'depot' => 'Dépôt',
            'retrait' => 'Retrait',
            'ouverture' => 'Ouverture',
            default => $this->type,
        };
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute()
    {
        return match($this->statut) {
            'reussie' => 'Réussie',
            'en_cours' => 'En cours',
            'echouee' => 'Échouée',
            'annulee' => 'Annulée',
            'initialiser' => 'Initialisée',
            default => $this->statut,
        };
    }

    /**
     * Statistiques par période
     */
    public static function getStatsByPeriod($startDate, $endDate)
    {
        return self::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                type,
                statut,
                COUNT(*) as nombre,
                SUM(montant) as total,
                AVG(montant) as moyenne
            ')
            ->groupBy('type', 'statut')
            ->get();
    }

    /**
     * Statistiques globales
     */
    public static function getGlobalStats()
    {
        return [
            'total_transactions' => self::count(),
            'transactions_reussies' => self::reussie()->count(),
            'transactions_echouees' => self::echouee()->count(),
            'montant_total' => self::reussie()->sum('montant'),
            'montant_depot' => self::depot()->reussie()->sum('montant'),
            'montant_retrait' => self::retrait()->reussie()->sum('montant'),
            'montant_ouverture' => self::ouverture()->reussie()->sum('montant'),
        ];
    }
}
