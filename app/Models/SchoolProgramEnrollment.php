<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProgramEnrollment extends Model
{
    protected $fillable = [
        'school_program_id',
        'client_id',
        'savings_account_id',
        'coupon_code',
        'coupon_value',
        'tier',
        'coupon_status',
        'balance_blocked',
        'blocked_until',
        'used_at',
        'used_by_affiliate_id',
        'enrolled_by',
    ];

    protected $casts = [
        'coupon_value'    => 'decimal:2',
        'balance_blocked' => 'decimal:2',
        'tier'            => 'integer',
        'blocked_until'   => 'datetime',
        'used_at'         => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(SchoolProgram::class, 'school_program_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function savingsAccount()
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function usedByAffiliate()
    {
        return $this->belongsTo(Affiliate::class, 'used_by_affiliate_id');
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    public function isActive(): bool
    {
        return $this->coupon_status === 'active';
    }

    public function isBlockExpired(): bool
    {
        return $this->blocked_until && now()->gt($this->blocked_until);
    }

    public function getStatusLabel(): string
    {
        return match ($this->coupon_status) {
            'active'    => 'Actif',
            'used'      => 'Utilisé',
            'expired'   => 'Expiré',
            'cancelled' => 'Annulé',
            default     => $this->coupon_status,
        };
    }
}
