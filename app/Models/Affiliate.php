<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'whatsapp',
        'code_parrain',
        'status',
        'code_verification',
        'email_verifie',
        'email_verifie_at',
        'solde_bonus',
        'nombre_parrainages',
        'motif_rejet',
        'approuve_at',
        'approuve_by',
    ];

    protected $casts = [
        'email_verifie' => 'boolean',
        'email_verifie_at' => 'datetime',
        'approuve_at' => 'datetime',
        'solde_bonus' => 'decimal:2',
        'nombre_parrainages' => 'integer',
    ];

    /**
     * Générer un code de vérification à 4 chiffres
     */
    public static function generateVerificationCode(): string
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Générer un code de parrainage unique
     */
    public static function generateCodeParrain(): string
    {
        do {
            $code = 'AFF' . strtoupper(Str::random(6));
        } while (self::where('code_parrain', $code)->exists());

        return $code;
    }

    /**
     * Relation: Clients parrainés
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'affiliate_id');
    }

    /**
     * Relation: Parrainages
     */
    public function parrainages()
    {
        return $this->hasMany(Parrainage::class);
    }

    /**
     * Relation: Paiements reçus
     */
    public function paiements()
    {
        return $this->hasMany(AffiliatePaiement::class);
    }

    /**
     * Relation: Utilisateur qui a approuvé
     */
    public function approuvePar()
    {
        return $this->belongsTo(User::class, 'approuve_by');
    }

    /**
     * Scopes
     */
    public function scopeEnAttente($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeApprouve($query)
    {
        return $query->where('status', 'approuve');
    }

    public function scopeActif($query)
    {
        return $query->where('status', 'approuve')->where('email_verifie', true);
    }

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getIsActifAttribute()
    {
        return $this->status === 'approuve' && $this->email_verifie;
    }

    public function getSoldeFormatteAttribute()
    {
        return number_format($this->solde_bonus, 2, ',', ' ') . ' GDS';
    }
}
