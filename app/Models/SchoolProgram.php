<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProgram extends Model
{
    protected $fillable = [
        'name',
        'description',
        'date_debut',
        'date_fin',
        'inscription_debut',
        'inscription_fin',
        'solde_minimum_epargne',
        'montant_blocage',
        'duree_blocage_jours',
        'tier1_seuil',
        'tier1_coupon',
        'tier2_seuil',
        'tier2_coupon',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date_debut'             => 'date',
        'date_fin'               => 'date',
        'inscription_debut'      => 'date',
        'inscription_fin'        => 'date',
        'solde_minimum_epargne'  => 'decimal:2',
        'montant_blocage'        => 'decimal:2',
        'duree_blocage_jours'    => 'integer',
        'tier1_seuil'            => 'decimal:2',
        'tier1_coupon'           => 'decimal:2',
        'tier2_seuil'            => 'decimal:2',
        'tier2_coupon'           => 'decimal:2',
    ];

    public function enrollments()
    {
        return $this->hasMany(SchoolProgramEnrollment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActif($query)
    {
        return $query->where('status', 'actif');
    }

    public function isActive(): bool
    {
        return $this->status === 'actif';
    }

    public function isInscriptionOpen(): bool
    {
        $today = now()->startOfDay();
        return $today->between($this->inscription_debut, $this->inscription_fin);
    }

    public function isCouponPeriodActive(): bool
    {
        $today = now()->startOfDay();
        return $today->between($this->date_debut, $this->date_fin);
    }
}
