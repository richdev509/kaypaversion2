<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'sender_name',
        'sender_country_code',
        'sender_phone',
        'sender_ninu',
        'sender_address',
        'sender_department_id',
        'sender_commune_id',
        'sender_city_id',
        'sender_account_id',
        'receiver_name',
        'receiver_country_code',
        'receiver_phone',
        'amount',
        'fees',
        'discount',
        'total_amount',
        'status',
        'created_by',
        'branch_id',
        'paid_by',
        'paid_at_branch_id',
        'paid_at',
        'receiver_ninu',
        'receiver_address',
        'receiver_department_id',
        'receiver_commune_id',
        'receiver_city_id',
        'note',
        'modified_by',
        'modified_at',
        'modification_history',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'is_disputed',
        'dispute_status',
        'dispute_reason',
        'disputed_by',
        'disputed_at',
        'dispute_resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'modified_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'disputed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_disputed' => 'boolean',
    ];

    /**
     * Générer un numéro de transfert unique (format Western Union: 10 chiffres)
     */
    public static function generateTransferNumber()
    {
        do {
            // Générer 10 chiffres aléatoires (comme Western Union)
            $number = str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (self::where('transfer_number', $number)->exists());

        return $number;
    }

    /**
     * Relations
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function paidAtBranch()
    {
        return $this->belongsTo(Branch::class, 'paid_at_branch_id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function disputedBy()
    {
        return $this->belongsTo(User::class, 'disputed_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function senderAccount()
    {
        return $this->belongsTo(Account::class, 'sender_account_id', 'account_id');
    }

    public function senderDepartment()
    {
        return $this->belongsTo(Department::class, 'sender_department_id');
    }

    public function senderCommune()
    {
        return $this->belongsTo(Commune::class, 'sender_commune_id');
    }

    public function senderCity()
    {
        return $this->belongsTo(City::class, 'sender_city_id');
    }

    public function receiverDepartment()
    {
        return $this->belongsTo(Department::class, 'receiver_department_id');
    }

    public function receiverCommune()
    {
        return $this->belongsTo(Commune::class, 'receiver_commune_id');
    }

    public function receiverCity()
    {
        return $this->belongsTo(City::class, 'receiver_city_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Accesseurs
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'paid' => 'Payé',
            'cancelled' => 'Annulé',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute()
    {
        $color = $this->status_badge_color;
        $label = $this->status_label;

        $classes = match($color) {
            'yellow' => 'bg-yellow-500 text-white dark:bg-yellow-600',
            'green' => 'bg-green-600 text-white dark:bg-green-700',
            'red' => 'bg-red-600 text-white dark:bg-red-700',
            default => 'bg-gray-500 text-white dark:bg-gray-600',
        };

        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $classes . '">' . $label . '</span>';
    }

    /**
     * Calculer les frais de transfert
     */
    public static function calculateFees($amount, $hasKaypaAccount = false)
    {
        $settings = TransferSetting::first();

        if (!$settings) {
            // Valeurs par défaut si pas de paramètres
            $feePercentage = 0;
            $feeFixed = 100;
            $discount = $hasKaypaAccount ? 10 : 0;
        } else {
            $feePercentage = $settings->transfer_fee_percentage;
            $feeFixed = $settings->transfer_fee_fixed;
            $discount = $hasKaypaAccount ? $settings->kaypa_client_discount : 0;
        }

        // Calculer les frais
        $fees = ($amount * $feePercentage / 100) + $feeFixed;

        // Appliquer la réduction si client Kaypa
        $discountAmount = $fees * $discount / 100;

        return [
            'fees' => round($fees, 2),
            'discount' => round($discountAmount, 2),
            'total' => round($amount + $fees - $discountAmount, 2),
        ];
    }
}
