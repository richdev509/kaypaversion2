<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    protected $fillable = [
        'account_id',
        'client_id',
        'plan_id',
        'montant_journalier',
        'date_debut',
        'date_fin',
        'status',
        'balance',
        'amount_after',
        'montant_dispo_retrait',
        'withdraw',
        'retrait_status',
        'credit_locked',
        'is_from_parrainage'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant_journalier' => 'decimal:2',
        'balance' => 'decimal:2',
        'amount_after' => 'float',
        'montant_dispo_retrait' => 'decimal:2',
        'withdraw' => 'decimal:2',
        'retrait_status' => 'boolean',
        'credit_locked' => 'float',
        'is_from_parrainage' => 'boolean',
    ];

    protected $appends = ['solde_virtuel'];

    /**
     * Attribut calculé: Solde actuel
     */
    public function getSoldeVirtuelAttribute()
    {
        return $this->amount_after ?? $this->balance ?? 0;
    }

    /**
     * Relation: Client propriétaire
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation: Plan d'épargne
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relation: Paiements (dépôts)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relation: Retraits (via account_transactions)
     */
    public function withdrawals()
    {
        return $this->hasMany(AccountTransaction::class, 'account_id', 'account_id')
                    ->where('type', AccountTransaction::TYPE_RETRAIT);
    }

    /**
     * Relation: Transactions
     */
    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class, 'account_id', 'account_id');
    }

    /**
     * Scope: Comptes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'actif');
    }

    /**
     * Scope: Comptes clos
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'clos');
    }

    /**
     * Scope: Comptes suspendus
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspendu');
    }

    /**
     * Scope: Comptes avec dette
     */
    public function scopeWithDebt($query)
    {
        return $query->where('retrait_status', 1)
            ->where('withdraw', '>', 0);
    }

    /**
     * Génération automatique de l'account_id
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->account_id)) {
                $account->account_id = self::generateAccountId();
            }
        });
    }

    /**
     * Générer un ID unique de compte
     */
    public static function generateAccountId(): string
    {
        do {
            $prefix = 'KP-';
            $random = random_int(1000000000, 9999999999);
            $id = $prefix . $random;

            $exists = self::where('account_id', $id)->exists();
        } while ($exists);

        return $id;
    }

    /**
     * Vérifier si le compte a une dette
     */
    public function hasDebt(): bool
    {
        return $this->retrait_status && $this->withdraw > 0;
    }

    /**
     * Obtenir le solde disponible pour retrait (montant libre)
     */
    public function getAvailableForWithdrawal(): float
    {
        // Retrait libre basé sur le solde uniquement (pas de vérification de dette)
        return $this->solde_virtuel;
    }

    /**
     * Vérifier si le plan est terminé
     */
    public function isPlanCompleted(): bool
    {
        return now()->greaterThanOrEqualTo($this->date_fin);
    }

    /**
     * Obtenir les jours restants
     */
    public function getDaysRemaining(): int
    {
        if ($this->isPlanCompleted()) {
            return 0;
        }
        return now()->diffInDays($this->date_fin, false);
    }
}
