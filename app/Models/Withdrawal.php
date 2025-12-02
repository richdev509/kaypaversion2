<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'account_id',
        'amount',
        'withdrawal_date',
        'method',
        'note',
        'penalty_applied',
    ];

    protected $casts = [
        'withdrawal_date' => 'date',
        'amount' => 'decimal:2',
        'penalty_applied' => 'boolean',
    ];

    /**
     * Relation: Le retrait appartient à un compte
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
