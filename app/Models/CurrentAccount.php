<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrentAccount extends Model
{
    protected $fillable = [
        'account_number',
        'client_id',
        'branch_id',
        'balance',
        'status',
        'last_fee_charged_at',
        'created_by',
    ];

    protected $casts = [
        'balance'             => 'decimal:2',
        'last_fee_charged_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->account_number)) {
                $account->account_number = self::generateAccountNumber();
            }
        });
    }

    public static function generateAccountNumber(): string
    {
        do {
            $number = 'KCC-' . random_int(1000000000, 9999999999);
        } while (self::where('account_number', $number)->exists());

        return $number;
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(CurrentAccountTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'actif');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspendu');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'cloture');
    }

    public function isActive(): bool
    {
        return $this->status === 'actif';
    }

    public function needsMonthlyFee(): bool
    {
        if (is_null($this->last_fee_charged_at)) {
            return true;
        }
        return $this->last_fee_charged_at->lt(now()->startOfMonth());
    }
}
