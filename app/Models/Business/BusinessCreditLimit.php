<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessCreditLimit extends Model
{
    protected $table = 'business_credit_limits';

    protected $fillable = [
        'business_id',
        'business_current_account_id',
        'rate_plan_id',
        'approved_limit',
        'credit_used',
        'taux_manuel',
        'duration_months',
        'starts_at',
        'expires_at',
        'status',
        'note',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'approved_limit'  => 'decimal:2',
        'credit_used'     => 'decimal:2',
        'taux_manuel'     => 'decimal:2',
        'duration_months' => 'integer',
        'starts_at'       => 'date',
        'expires_at'      => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function ($credit) {
            if (empty($credit->uuid)) {
                $credit->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    const STATUS_PENDING   = 'pending';
    const STATUS_ACTIVE    = 'active';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CLOSED    = 'closed';

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function account()
    {
        return $this->belongsTo(BusinessCurrentAccount::class, 'business_current_account_id');
    }

    public function ratePlan()
    {
        return $this->belongsTo(BusinessCreditRatePlan::class, 'rate_plan_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function interestCharges()
    {
        return $this->hasMany(BusinessCreditInterestCharge::class, 'business_credit_limit_id');
    }

    public function alerts()
    {
        return $this->hasMany(BusinessCreditAlert::class, 'business_credit_limit_id');
    }

    public function actionLogs()
    {
        return $this->hasMany(BusinessCreditActionLog::class, 'business_credit_limit_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getAvailableCredit(): float
    {
        return (float) $this->approved_limit - (float) $this->credit_used;
    }

    public function getEffectiveTaux(): float
    {
        return $this->taux_manuel !== null
            ? (float) $this->taux_manuel
            : ($this->ratePlan ? (float) $this->ratePlan->taux_mensuel : 0);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING   => 'En attente',
            self::STATUS_ACTIVE    => 'Actif',
            self::STATUS_EXPIRED   => 'Expiré',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_CLOSED    => 'Clôturé',
            default                => $this->status,
        };
    }
}
