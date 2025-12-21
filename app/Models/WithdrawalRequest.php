<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_id',
        'account_id',
        'client_id',
        'amount',
        'method',
        'wallet_phone',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'status',
        'admin_note',
        'processed_by',
        'processed_at',
        'transaction_id',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    // Méthodes utilitaires
    public static function generateReferenceId()
    {
        $lastRequest = self::orderBy('id', 'desc')->first();
        $number = $lastRequest ? intval(substr($lastRequest->reference_id, 3)) + 1 : 1;
        return 'WD_' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeProcessed()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'processing' => 'En traitement',
            'completed' => 'Complété',
            'rejected' => 'Rejeté',
            'cancelled' => 'Annulé',
            default => $this->status,
        };
    }

    public function getMethodLabel()
    {
        return match($this->method) {
            'wallet' => 'Portefeuille Mobile',
            'bank' => 'Virement Bancaire',
            default => $this->method,
        };
    }
}
