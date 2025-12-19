<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferSetting extends Model
{
    protected $fillable = [
        'min_amount',
        'max_amount',
        'transfer_fee_percentage',
        'transfer_fee_fixed',
        'kaypa_client_discount',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'transfer_fee_percentage' => 'decimal:2',
        'transfer_fee_fixed' => 'decimal:2',
        'kaypa_client_discount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Obtenir les paramètres actifs
     */
    public static function getSettings()
    {
        return self::where('is_active', true)->first() ?? self::first();
    }
}
