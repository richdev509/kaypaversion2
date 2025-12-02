<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FundMovement extends Model
{
    protected $fillable = [
        'reference',
        'type',
        'amount',
        'source_branch_id',
        'destination_branch_id',
        'source_type',
        'external_source',
        'reason',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Constantes de statut
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    // Constantes de type
    public const TYPE_IN = 'IN';
    public const TYPE_OUT = 'OUT';

    // Constantes de source
    public const SOURCE_SUCCURSALE = 'SUCCURSALE';
    public const SOURCE_BANQUE = 'BANQUE';
    public const SOURCE_EXTERNE = 'EXTERNE';
    public const SOURCE_INITIAL = 'INITIAL';

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movement) {
            if (empty($movement->reference)) {
                $movement->reference = self::generateReference();
            }
        });
    }

    /**
     * Générer une référence unique pour le mouvement
     */
    public static function generateReference(): string
    {
        $date = now()->format('Ymd');
        $lastMovement = self::where('reference', 'LIKE', "FMV-{$date}-%")
            ->orderBy('reference', 'desc')
            ->first();

        if ($lastMovement) {
            $lastNumber = (int) substr($lastMovement->reference, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "FMV-{$date}-{$newNumber}";
    }

    /**
     * Relation: Branche source
     */
    public function sourceBranch()
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    /**
     * Relation: Branche destination
     */
    public function destinationBranch()
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    /**
     * Relation: Créé par
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation: Approuvé par
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: En attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Rejetés
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope: Par branche
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('source_branch_id', $branchId)
              ->orWhere('destination_branch_id', $branchId);
        });
    }

    /**
     * Vérifier si le mouvement est en attente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifier si le mouvement est approuvé
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Vérifier si le mouvement est rejeté
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Obtenir le badge de statut
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>',
            self::STATUS_APPROVED => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approuvé</span>',
            self::STATUS_REJECTED => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejeté</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inconnu</span>',
        };
    }

    /**
     * Obtenir le badge de type
     */
    public function getTypeBadgeAttribute(): string
    {
        return match($this->type) {
            self::TYPE_IN => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Entrée</span>',
            self::TYPE_OUT => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Sortie</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inconnu</span>',
        };
    }
}
