<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessCurrentAccountTransaction extends Model
{
    protected $table = 'business_current_account_transactions';

    protected $fillable = [
        'transaction_id',
        'account_number',
        'business_current_account_id',
        'business_id',
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

    const TYPE_DEPOT                = 'DEPOT';
    const TYPE_RETRAIT              = 'RETRAIT';
    const TYPE_CREDIT_DECAISSEMENT  = 'CREDIT_DECAISSEMENT';
    const TYPE_CREDIT_REMBOURSEMENT = 'CREDIT_REMBOURSEMENT';
    const TYPE_INTERET_CREDIT       = 'INTERET_CREDIT';
    const TYPE_PENALITE             = 'PENALITE';
    const TYPE_PAYROLL              = 'PAYROLL';
    const TYPE_FRAIS_OUVERTURE      = 'FRAIS_OUVERTURE';

    const CREDIT_TYPES = [
        self::TYPE_DEPOT,
        self::TYPE_CREDIT_DECAISSEMENT,
    ];

    const DEBIT_TYPES = [
        self::TYPE_RETRAIT,
        self::TYPE_CREDIT_REMBOURSEMENT,
        self::TYPE_INTERET_CREDIT,
        self::TYPE_PENALITE,
        self::TYPE_PAYROLL,
        self::TYPE_FRAIS_OUVERTURE,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($tx) {
            if (empty($tx->transaction_id)) {
                $tx->transaction_id = (string) Str::ulid();
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(BusinessCurrentAccount::class, 'business_current_account_id');
    }

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCredit(): bool
    {
        return in_array($this->type, self::CREDIT_TYPES);
    }

    public function isDebit(): bool
    {
        return in_array($this->type, self::DEBIT_TYPES);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_DEPOT                => 'Dépôt',
            self::TYPE_RETRAIT              => 'Retrait',
            self::TYPE_CREDIT_DECAISSEMENT  => 'Décaissement crédit',
            self::TYPE_CREDIT_REMBOURSEMENT => 'Remboursement crédit',
            self::TYPE_INTERET_CREDIT       => 'Intérêts crédit',
            self::TYPE_PENALITE             => 'Pénalité',
            self::TYPE_PAYROLL              => 'Payroll',
            self::TYPE_FRAIS_OUVERTURE      => "Frais d'ouverture",
            default                         => $this->type,
        };
    }
}
