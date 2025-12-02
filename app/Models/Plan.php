<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'montant_par_jour',
        'duree',
        'montant_ouverture',
        'retrait_autorise',
        'jour_min_retrait',
        'pourcentage_retrait_partiel',
        'frais_jour_partiel',
        'frais_jour_total'
    ];

    protected $casts = [
        'montant_par_jour' => 'decimal:2',
        'duree' => 'integer',
        'montant_ouverture' => 'decimal:2',
        'retrait_autorise' => 'boolean',
        'jour_min_retrait' => 'integer',
        'pourcentage_retrait_partiel' => 'integer',
        'frais_jour_partiel' => 'integer',
        'frais_jour_total' => 'integer',
    ];

    /**
     * Relation: Comptes utilisant ce plan
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Relation: Montants journaliers disponibles pour ce plan
     */
    public function montants()
    {
        return $this->hasMany(PlanMontant::class);
    }

    /**
     * Calculer le montant total prévu pour ce plan
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->montant_par_jour * $this->duree;
    }

    /**
     * Obtenir la description du plan
     */
    public function getDescriptionAttribute(): string
    {
        return "{$this->name} - {$this->duree} jours @ {$this->montant_par_jour} HTG/jour";
    }

    /**
     * Vérifier si le retrait est autorisé
     */
    public function isWithdrawalAllowed(): bool
    {
        return (bool) $this->retrait_autorise;
    }

    /**
     * Obtenir le montant maximum de retrait partiel (en pourcentage)
     */
    public function getMaxPartialWithdrawalPercentage(): int
    {
        return $this->pourcentage_retrait_partiel;
    }

    /**
     * Calculer la pénalité pour retrait partiel (en jours)
     */
    public function getPartialWithdrawalPenalty(float $amount): float
    {
        return $this->montant_par_jour * $this->frais_jour_partiel;
    }

    /**
     * Calculer la pénalité pour retrait total anticipé (en jours)
     */
    public function getTotalWithdrawalPenalty(): float
    {
        return $this->montant_par_jour * $this->frais_jour_total;
    }

    /**
     * Scope: Plans actifs (avec retrait autorisé)
     */
    public function scopeActive($query)
    {
        return $query->where('retrait_autorise', 1);
    }

    /**
     * Scope: Plans par durée
     */
    public function scopeByDuration($query, int $days)
    {
        return $query->where('duree', $days);
    }
}
