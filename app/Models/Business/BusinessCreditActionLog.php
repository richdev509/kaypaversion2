<?php

namespace App\Models\Business;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessCreditActionLog extends Model
{
    protected $table = 'business_credit_action_logs';

    protected $fillable = [
        'business_credit_limit_id',
        'business_credit_alert_id',
        'business_id',
        'action',
        'note',
        'done_by',
    ];

    const ACTION_NOTE       = 'note';
    const ACTION_APPEL      = 'appel';
    const ACTION_EMAIL      = 'email';
    const ACTION_VISITE     = 'visite';
    const ACTION_ESCALADE   = 'escalade';
    const ACTION_RESOLUTION = 'resolution';
    const ACTION_APPROBATION = 'approbation';
    const ACTION_REJET      = 'rejet';

    public function credit()
    {
        return $this->belongsTo(BusinessCreditLimit::class, 'business_credit_limit_id');
    }

    public function alert()
    {
        return $this->belongsTo(BusinessCreditAlert::class, 'business_credit_alert_id');
    }

    public function business()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_id');
    }

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }

    public function getActionLabel(): string
    {
        return match($this->action) {
            self::ACTION_NOTE        => 'Note interne',
            self::ACTION_APPEL       => 'Appel',
            self::ACTION_EMAIL       => 'E-mail',
            self::ACTION_VISITE      => 'Visite',
            self::ACTION_ESCALADE    => 'Escalade',
            self::ACTION_RESOLUTION  => 'Résolution',
            self::ACTION_APPROBATION => 'Approbation',
            self::ACTION_REJET       => 'Rejet',
            default                  => $this->action,
        };
    }
}
