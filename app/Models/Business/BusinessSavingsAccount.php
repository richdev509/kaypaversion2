<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessSavingsAccount extends Model
{
    protected $table = 'business_savings_accounts';

    protected $fillable = [
        'account_number',
        'business_id',
        'balance',
        'status',
        'last_interest_at',
        'created_by',
    ];

    protected $casts = [
        'balance'          => 'decimal:2',
        'last_interest_at' => 'datetime',
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
            $number = 'KEB' . $year . $chars;
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
