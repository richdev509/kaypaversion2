<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessPayrollBatch extends Model
{
    protected $table = 'business_payroll_batches';

    protected $fillable = [
        'reference',
        'business_id',
        'period_label',
        'total_amount',
        'employee_count',
        'status',
        'approved_by',
        'approved_at',
        'processed_at',
        'note',
        'created_by',
    ];

    protected $casts = [
        'total_amount'   => 'decimal:2',
        'employee_count' => 'integer',
        'approved_at'    => 'datetime',
        'processed_at'   => 'datetime',
    ];

    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED         = 'approved';
    const STATUS_PROCESSING       = 'processing';
    const STATUS_COMPLETED        = 'completed';
    const STATUS_FAILED           = 'failed';
    const STATUS_CANCELLED        = 'cancelled';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($batch) {
            if (empty($batch->reference)) {
                $batch->reference = self::generateReference();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(Str::random(8));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(BusinessPayrollItem::class, 'business_payroll_batch_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT            => 'Brouillon',
            self::STATUS_PENDING_APPROVAL => 'En attente d\'approbation',
            self::STATUS_APPROVED         => 'Approuvé',
            self::STATUS_PROCESSING       => 'En traitement',
            self::STATUS_COMPLETED        => 'Complété',
            self::STATUS_FAILED           => 'Échec',
            self::STATUS_CANCELLED        => 'Annulé',
            default                       => $this->status,
        };
    }
}
