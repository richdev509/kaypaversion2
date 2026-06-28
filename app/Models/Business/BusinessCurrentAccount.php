<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessCurrentAccount extends Model
{
    protected $table = 'business_current_accounts';

    protected $fillable = [
        'account_number',
        'business_id',
        'balance',
        'status',
        'last_flux_at',
        'created_by',
    ];

    protected $casts = [
        'balance'      => 'decimal:2',
        'last_flux_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->uuid)) {
                $account->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($account->account_number)) {
                $account->account_number = self::generateAccountNumber();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function generateAccountNumber(): string
    {
        $year = date('y');
        do {
            $chars = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
            $number = 'KCB' . $year . $chars;
        } while (self::where('account_number', $number)->exists());

        return $number;
    }

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(BusinessCurrentAccountTransaction::class, 'business_current_account_id');
    }

    public function creditLimits()
    {
        return $this->hasMany(BusinessCreditLimit::class, 'business_current_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'actif');
    }

    public function isActive(): bool
    {
        return $this->status === 'actif';
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'actif'    => 'Actif',
            'suspendu' => 'Suspendu',
            'cloture'  => 'Clôturé',
            default    => $this->status,
        };
    }
}
