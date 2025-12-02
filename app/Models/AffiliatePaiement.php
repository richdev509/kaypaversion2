<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliatePaiement extends Model
{
    protected $fillable = [
        'affiliate_id',
        'montant',
        'methode',
        'note',
        'effectue_by',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    /**
     * Relation: Affilié
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Relation: Utilisateur qui a effectué le paiement
     */
    public function effectuePar()
    {
        return $this->belongsTo(User::class, 'effectue_by');
    }

    /**
     * Accesseur: Montant formatté
     */
    public function getMontantFormatteAttribute()
    {
        return number_format($this->montant, 2, ',', ' ') . ' GDS';
    }
}
