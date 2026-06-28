<?php

namespace App\Models\Business;

use App\Models\Client;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessEntity extends Model
{
    protected $table = 'business_entities';

    protected $fillable = [
        'business_number',
        'name',
        'legal_name',
        'rccm',
        'nif',
        'phone',
        'email',
        'address',
        'city',
        'owner_client_id',
        'branch_id',
        'status_kyc',
        'status',
        'profile',
        'months_active',
        'kyc_verified_by',
        'kyc_verified_at',
        'created_by',
    ];

    protected $casts = [
        'kyc_verified_at' => 'datetime',
        'months_active'   => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($entity) {
            if (empty($entity->uuid)) {
                $entity->uuid = (string) Str::uuid();
            }
            if (empty($entity->business_number)) {
                $entity->business_number = self::generateBusinessNumber();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function generateBusinessNumber(): string
    {
        do {
            $number = 'BIZ-' . strtoupper(Str::random(8));
        } while (self::where('business_number', $number)->exists());

        return $number;
    }

    public function ownerClient()
    {
        return $this->belongsTo(Client::class, 'owner_client_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kycVerifier()
    {
        return $this->belongsTo(User::class, 'kyc_verified_by');
    }

    public function businessUsers()
    {
        return $this->hasMany(BusinessUser::class, 'business_id');
    }

    public function currentAccount()
    {
        return $this->hasOne(BusinessCurrentAccount::class, 'business_id');
    }

    public function savingsAccount()
    {
        return $this->hasOne(BusinessSavingsAccount::class, 'business_id');
    }

    public function creditLimits()
    {
        return $this->hasMany(BusinessCreditLimit::class, 'business_id');
    }

    public function employees()
    {
        return $this->hasMany(BusinessEmployee::class, 'business_id');
    }

    public function payrollBatches()
    {
        return $this->hasMany(BusinessPayrollBatch::class, 'business_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeKycVerified($query)
    {
        return $query->where('status_kyc', 'verified');
    }

    public function scopeKycPending($query)
    {
        return $query->where('status_kyc', 'pending');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isKycVerified(): bool
    {
        return $this->status_kyc === 'verified';
    }

    public function isOperational(): bool
    {
        return $this->isActive() && $this->isKycVerified();
    }

    public function getStatusKycLabel(): string
    {
        return match($this->status_kyc) {
            'pending'  => 'En attente',
            'verified' => 'Vérifié',
            'rejected' => 'Rejeté',
            default    => $this->status_kyc,
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active'    => 'Actif',
            'suspended' => 'Suspendu',
            'closed'    => 'Clôturé',
            default     => $this->status,
        };
    }

    public function getProfileLabel(): string
    {
        return match($this->profile) {
            'standard' => 'Standard',
            'etabli'   => 'Établi',
            'premium'  => 'Premium',
            default    => $this->profile,
        };
    }
}
