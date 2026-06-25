<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SavingsAccountTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'savings_account_number',
        'savings_account_id',
        'client_id',
        'type',
        'amount',
        'balance_after',
        'method',
        'reference',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    const TYPE_DEPOT          = 'DEPOT';
    const TYPE_RETRAIT        = 'RETRAIT';
    const TYPE_FRAIS_OUVERTURE = 'FRAIS_OUVERTURE';
    const TYPE_INTERET        = 'INTERET';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($tx) {
            if (empty($tx->transaction_id)) {
                $tx->transaction_id = (string) Str::ulid();
            }
        });
    }

    public function savingsAccount()
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCredit(): bool
    {
        return in_array($this->type, [self::TYPE_DEPOT, self::TYPE_INTERET]);
    }

    public function isDebit(): bool
    {
        return in_array($this->type, [self::TYPE_RETRAIT, self::TYPE_FRAIS_OUVERTURE]);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_DEPOT           => 'Dépôt',
            self::TYPE_RETRAIT         => 'Retrait',
            self::TYPE_FRAIS_OUVERTURE => "Frais d'ouverture",
            self::TYPE_INTERET         => 'Intérêt mensuel',
            default                    => $this->type,
        };
    }
}
