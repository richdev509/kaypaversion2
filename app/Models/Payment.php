<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'account_id',
        'amount',
        'payment_date',
        'method',
        'reference',
        'type',
        'created_by',
        'note',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relation: Le paiement appartient à un compte
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Relation: Le paiement a été créé par un utilisateur
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
