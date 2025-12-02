<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parrainage extends Model
{
    protected $fillable = [
        'affiliate_id',
        'client_id',
        'account_id',
        'code_utilise',
        'bonus_gagne',
        'status',
        'valide_at',
        'paye_at',
        'paye_by',
    ];

    protected $casts = [
        'bonus_gagne' => 'decimal:2',
        'valide_at' => 'datetime',
        'paye_at' => 'datetime',
    ];

    /**
     * Relation: Affilié
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Relation: Client parrainé
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation: Compte ouvert
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Relation: Utilisateur qui a payé
     */
    public function payePar()
    {
        return $this->belongsTo(User::class, 'paye_by');
    }

    /**
     * Scopes
     */
    public function scopeEnAttente($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeValide($query)
    {
        return $query->where('status', 'valide');
    }

    public function scopePaye($query)
    {
        return $query->where('status', 'paye');
    }
}
