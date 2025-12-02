<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'cash_balance',
    ];

    /**
     * Relation: Une branche a plusieurs clients
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Relation: Une branche a plusieurs comptes (via clients)
     */
    public function accounts()
    {
        return $this->hasManyThrough(Account::class, Client::class, 'branch_id', 'client_id', 'id', 'id');
    }

    /**
     * Relation: Une branche a plusieurs utilisateurs
     */
    public function users()
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    /**
     * Scope: Recherche par nom ou adresse
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%");
        });
    }

    /**
     * Obtenir le nombre de clients actifs
     */
    public function getActiveClientsCountAttribute()
    {
        return $this->clients()->whereHas('accounts', function($q) {
            $q->where('status', 'actif');
        })->count();
    }

    /**
     * Obtenir le nombre total de comptes
     */
    public function getTotalAccountsCountAttribute()
    {
        return $this->clients()->withCount('accounts')->get()->sum('accounts_count');
    }
}
