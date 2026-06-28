<?php

namespace App\Models\Business;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

class BusinessUser extends Model
{
    protected $table = 'business_users';

    protected $fillable = [
        'business_id',
        'client_id',
        'role',
        'can_approve_payroll',
        'can_request_credit',
        'is_active',
        'invited_by',
    ];

    protected $casts = [
        'can_approve_payroll' => 'boolean',
        'can_request_credit'  => 'boolean',
        'is_active'           => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(BusinessUser::class, 'invited_by');
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function getRoleLabel(): string
    {
        return match($this->role) {
            'owner'       => 'Propriétaire',
            'accountant'  => 'Comptable',
            'hr'          => 'RH',
            'viewer'      => 'Lecteur',
            default       => $this->role,
        };
    }
}
