<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanMontant extends Model
{
    protected $table = 'plan_montant'; // Pluriel

    protected $fillable = [
        'plan_id',
        'montant',
        'interet',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'interet' => 'decimal:2',
    ];

    /**
     * Relation: Plan parent
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Calculer le montant total avec intérêt
     */
    public function getTotalAvecInteretAttribute()
    {
        return $this->montant + $this->interet;
    }

    /**
     * Formater le montant pour l'affichage
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->montant, 2, ',', ' ') . ' HTG';
    }

    /**
     * Obtenir la description complète
     */
    public function getDescriptionAttribute()
    {
        $total = $this->total_avec_interet;
        return number_format($this->montant, 2, ',', ' ') . " HTG + " . number_format($this->interet, 2, ',', ' ') . " HTG intérêt = " . number_format($total, 2, ',', ' ') . " HTG";
    }
}
