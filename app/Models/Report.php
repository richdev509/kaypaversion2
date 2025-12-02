<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'type',
        'period_type',
        'start_date',
        'end_date',
        'branch_id',
        'user_id',
        'total_amount',
        'total_count',
        'data',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'data' => 'array',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relation: Rapport appartient à une branche
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relation: Rapport créé par un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filtrer par type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filtrer par période
     */
    public function scopePeriodType($query, string $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope: Filtrer par branche
     */
    public function scopeBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope: Filtrer par plage de dates
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'deposit' => 'Dépôts',
            'withdrawal' => 'Retraits',
            'all' => 'Tous',
            default => $this->type,
        };
    }

    /**
     * Obtenir le label de la période
     */
    public function getPeriodLabel(): string
    {
        return match($this->period_type) {
            'daily' => 'Journalier',
            'weekly' => 'Hebdomadaire',
            'monthly' => 'Mensuel',
            default => $this->period_type,
        };
    }
}
