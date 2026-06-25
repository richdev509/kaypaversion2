<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CurrentAccountTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'current_account_number',
        'current_account_id',
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
        'amount'       => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    const TYPE_DEPOT          = 'DEPOT';
    const TYPE_RETRAIT        = 'RETRAIT';
    const TYPE_FRAIS_OUVERTURE = 'FRAIS_OUVERTURE';
    const TYPE_FRAIS_SERVICE  = 'FRAIS_SERVICE';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($tx) {
            if (empty($tx->transaction_id)) {
                $tx->transaction_id = (string) Str::ulid();
            }
        });
    }

    public function currentAccount()
    {
        return $this->belongsTo(CurrentAccount::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDebit(): bool
    {
        return in_array($this->type, [self::TYPE_RETRAIT, self::TYPE_FRAIS_OUVERTURE, self::TYPE_FRAIS_SERVICE]);
    }

    public function isCredit(): bool
    {
        return $this->type === self::TYPE_DEPOT;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_DEPOT           => 'Dépôt',
            self::TYPE_RETRAIT         => 'Retrait',
            self::TYPE_FRAIS_OUVERTURE => 'Frais d\'ouverture',
            self::TYPE_FRAIS_SERVICE   => 'Frais de service',
            default                    => $this->type,
        };
    }
}
