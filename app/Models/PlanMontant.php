<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanMontant extends Model
{
    protected $table = 'plan_montant'; // Pluriel

    protected $fillable = [
        'plan_id',
        'montant_par_jour',
    ];

    protected $casts = [
        'montant_par_jour' => 'integer',
    ];

    /**
     * Relation: Plan parent
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Calculer le montant total prévu (montant_par_jour * durée du plan)
     */
    public function getTotalPrevuAttribute()
    {
        return $this->montant_par_jour * ($this->plan->duree ?? 0);
    }

    /**
     * Formater le montant pour l'affichage
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->montant_par_jour, 0, ',', ' ') . ' HTG/jour';
    }

    /**
     * Obtenir la description complète
     */
    public function getDescriptionAttribute()
    {
        $totalPrevu = $this->total_prevu;
        return number_format($this->montant_par_jour, 0, ',', ' ') . " HTG/jour → Total: " . number_format($totalPrevu, 0, ',', ' ') . " HTG";
    }
}
