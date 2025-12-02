<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccountTransaction extends Model
{
    protected $fillable = [
        'id_transaction',
        'account_id',
        'client_id',
        'type',
        'amount',
        'amount_after',
        'mode',
        'method',
        'reference',
        'note',
        'created_by',
        'status',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_after' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    // Constantes de type
    public const TYPE_PAIEMENT = 'PAIEMENT';
    public const TYPE_RETRAIT = 'RETRAIT';
    public const TYPE_AJUSTEMENT = 'AJUSTEMENT';
    public const TYPE_PAIEMENT_INITIAL = 'Paiement initial';

    // Constantes de mode
    public const MODE_PARTIEL = 'partiel';
    public const MODE_TOTAL = 'total';

    /**
     * Génération automatique de l'ID transaction
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tx) {
            if (empty($tx->id_transaction)) {
                $tx->id_transaction = (string) Str::ulid();
            }
        });
    }

    /**
     * Relation: Compte
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    /**
     * Relation: Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation: Créateur (utilisateur)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Transactions pour un compte
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope: Paiements uniquement
     */
    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAIEMENT);
    }

    /**
     * Scope: Retraits uniquement
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_RETRAIT);
    }

    /**
     * Scope: Transactions récentes
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Vérifier si c'est un dépôt
     */
    public function isDeposit(): bool
    {
        return $this->type === self::TYPE_PAIEMENT;
    }

    /**
     * Vérifier si c'est un retrait
     */
    public function isWithdrawal(): bool
    {
        return $this->type === self::TYPE_RETRAIT;
    }

    /**
     * Obtenir le montant formaté
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->amount >= 0 ? '+' : '';
        return $sign . number_format($this->amount, 2) . ' HTG';
    }
}
