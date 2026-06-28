<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessCreditAlert extends Model
{
    protected $table = 'business_credit_alerts';

    protected $fillable = [
        'business_credit_limit_id',
        'business_id',
        'level',
        'days_without_flux',
        'status',
        'resolved_at',
        'resolved_by',
        'note',
    ];

    protected $casts = [
        'days_without_flux' => 'integer',
        'resolved_at'       => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function ($alert) {
            if (empty($alert->uuid)) {
                $alert->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    const LEVEL_YELLOW  = 'yellow';
    const LEVEL_ORANGE  = 'orange';
    const LEVEL_RED     = 'red';
    const LEVEL_DEFAULT = 'default';

    const STATUS_OPEN       = 'open';
    const STATUS_CONTACTED  = 'contacted';
    const STATUS_RESOLVED   = 'resolved';
    const STATUS_ESCALATED  = 'escalated';

    public function credit()
    {
        return $this->belongsTo(BusinessCreditLimit::class, 'business_credit_limit_id');
    }

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function actionLogs()
    {
        return $this->hasMany(BusinessCreditActionLog::class, 'business_credit_alert_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function getLevelLabel(): string
    {
        return match($this->level) {
            self::LEVEL_YELLOW  => 'Attention',
            self::LEVEL_ORANGE  => 'Avertissement',
            self::LEVEL_RED     => 'Critique',
            self::LEVEL_DEFAULT => 'Information',
            default             => $this->level,
        };
    }

    public function getLevelColor(): string
    {
        return match($this->level) {
            self::LEVEL_YELLOW  => 'yellow',
            self::LEVEL_ORANGE  => 'orange',
            self::LEVEL_RED     => 'red',
            self::LEVEL_DEFAULT => 'blue',
            default             => 'gray',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_OPEN      => 'Ouvert',
            self::STATUS_CONTACTED => 'Contacté',
            self::STATUS_RESOLVED  => 'Résolu',
            self::STATUS_ESCALATED => 'Escaladé',
            default                => $this->status,
        };
    }
}
