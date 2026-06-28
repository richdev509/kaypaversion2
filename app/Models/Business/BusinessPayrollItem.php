<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class BusinessPayrollItem extends Model
{
    protected $table = 'business_payroll_items';

    protected $fillable = [
        'business_payroll_batch_id',
        'business_employee_id',
        'business_id',
        'destination_account',
        'amount',
        'status',
        'transaction_id',
        'processed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED  = 'failed';
    const STATUS_SKIPPED = 'skipped';

    public function batch()
    {
        return $this->belongsTo(BusinessPayrollBatch::class, 'business_payroll_batch_id');
    }

    public function employee()
    {
        return $this->belongsTo(BusinessEmployee::class, 'business_employee_id');
    }

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_SUCCESS => 'Succès',
            self::STATUS_FAILED  => 'Échec',
            self::STATUS_SKIPPED => 'Ignoré',
            default              => $this->status,
        };
    }
}
