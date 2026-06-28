<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessCreditRatePlan extends Model
{
    protected $table = 'business_credit_rate_plans';

    protected $fillable = [
        'name',
        'profile',
        'duration_min_months',
        'duration_max_months',
        'taux_mensuel',
        'taux_penalite',
        'effective_from',
        'effective_to',
        'is_active',
        'note',
        'created_by',
    ];

    protected $casts = [
        'taux_mensuel'        => 'decimal:2',
        'taux_penalite'       => 'decimal:2',
        'effective_from'      => 'date',
        'effective_to'        => 'date',
        'is_active'           => 'boolean',
        'duration_min_months' => 'integer',
        'duration_max_months' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('effective_from', '<=', now()->toDateString())
                     ->where(function ($q) {
                         $q->whereNull('effective_to')
                           ->orWhere('effective_to', '>=', now()->toDateString());
                     });
    }

    public static function findForBusiness(string $profile, int $durationMonths): ?self
    {
        return self::active()
            ->where('profile', $profile)
            ->where('duration_min_months', '<=', $durationMonths)
            ->where('duration_max_months', '>=', $durationMonths)
            ->orderBy('effective_from', 'desc')
            ->first();
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
