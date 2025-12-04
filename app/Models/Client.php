<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'kaypa_identity_id',
        'first_name',
        'last_name',
        'middle_name',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'birth_date',
        'status_kyc',
        'card_number',
        'numero_carte',
        'phone',
        'email',
        'branch_id',
        'date_emission',
        'date_expiration',
        'document_id_type',
        'document_id_number',
        'front_id_path',
        'back_id_path',
        'selfie_path',
        'client_id',
        'id_nif_cin',
        'id_nif_cin_file_path',
        'kyc',
        'department_id',
        'commune_id',
        'city_id',
        'profil_path',
        'address',
        'password',
        'password_reset',
        'area_code',
        'code_parrain',
        'affiliate_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'birth_date' => 'date',
        'date_emission' => 'date',
        'date_expiration' => 'date',
        'kyc' => 'boolean',
        'password_reset' => 'boolean',
    ];

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        return implode(' ', $parts);
    }

    /**
     * Relation: Accounts du client
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Relation: Transactions via comptes
     */
    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class);
    }

    /**
     * Relation: Client appartient à un département
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relation: Client appartient à une commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Relation: Client appartient à une ville
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Relation: Client appartient à une branche
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relation: Client parrainé par un affilié
     */
    public function affiliate()
    {
        return $this->belongsTo(\App\Models\Affiliate::class, 'affiliate_id');
    }

    /**
     * Scope: Recherche multi-critères (inclut numéro de compte)
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('middle_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('numero_carte', 'like', "%{$search}%")
              ->orWhere('card_number', 'like', "%{$search}%")
              ->orWhere('document_id_number', 'like', "%{$search}%")
              ->orWhere('id_nif_cin', 'like', "%{$search}%")
              ->orWhere('client_id', 'like', "%{$search}%")
              ->orWhereHas('accounts', function($q) use ($search) {
                  $q->where('account_id', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope: KYC vérifié
     */
    public function scopeVerified($query)
    {
        return $query->where('status_kyc', 'verified');
    }

    /**
     * Scope: KYC en attente
     */
    public function scopePending($query)
    {
        return $query->where('status_kyc', 'pending');
    }
}
