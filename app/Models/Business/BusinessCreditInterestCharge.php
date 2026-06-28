<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessCreditInterestCharge extends Model
{
    protected $table = 'business_credit_interest_charges';

    protected $fillable = [
        'business_credit_limit_id',
        'business_id',
        'period_start',
        'period_end',
        'avg_balance_used',
        'taux_applied',
        'total_due',
        'status',
        'transaction_id',
        'debited_at',
        'note',
        'processed_by',
    ];

    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'avg_balance_used' => 'decimal:2',
        'taux_applied'     => 'decimal:2',
        'total_due'        => 'decimal:2',
        'debited_at'       => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_DEBITED = 'debited';
    const STATUS_FAILED  = 'failed';
    const STATUS_WAIVED  = 'waived';

    public function credit()
    {
        return $this->belongsTo(BusinessCreditLimit::class, 'business_credit_limit_id');
    }

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_DEBITED => 'Débité',
            self::STATUS_FAILED  => 'Échec',
            self::STATUS_WAIVED  => 'Annulé',
            default              => $this->status,
        };
    }
}
