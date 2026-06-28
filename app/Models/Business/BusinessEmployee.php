<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessEmployee extends Model
{
    protected $table = 'business_employees';

    protected $fillable = [
        'business_id',
        'full_name',
        'kaypa_account_number',
        'poste',
        'salaire_defaut',
        'is_active',
        'note',
        'added_by',
    ];

    protected $casts = [
        'salaire_defaut' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function payrollItems()
    {
        return $this->hasMany(BusinessPayrollItem::class, 'business_employee_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
