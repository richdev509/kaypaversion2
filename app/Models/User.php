<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'telephone',
        'commercant_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Relation: Utilisateur appartient à une branche
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relation: Appareils de confiance de l'utilisateur
     */
    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Relation: Appareils de confiance uniquement
     */
    public function trustedDevices()
    {
        return $this->hasMany(UserDevice::class)->trusted();
    }

    /**
     * Relation: Transactions créées par cet utilisateur
     */
    public function createdTransactions()
    {
        return $this->hasMany(AccountTransaction::class, 'created_by');
    }

    /**
     * Relation: Paiements créés par cet utilisateur
     */
    public function createdPayments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    /**
     * Scope: Recherche multi-critères
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('telephone', 'like', "%{$search}%");
        });
    }

    /**
     * Définition des rôles disponibles
     */
    public static function availableRoles(): array
    {
        return [
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'comptable' => 'Comptable',
            'agent' => 'Agent',
            'viewer' => 'Viewer',
            'support' => 'Support Client',
        ];
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Vérifier si l'utilisateur est manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Vérifier si l'utilisateur est agent
     */
    public function isAgent(): bool
    {
        return $this->hasRole('agent');
    }

    /**
     * Vérifier si l'utilisateur est support
     */
    public function isSupport(): bool
    {
        return $this->hasRole('support');
    }

    /**
     * Vérifier si l'utilisateur est client
     */
    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    /**
     * Obtenir le nom du rôle en français
     */
    public function getRoleNameAttribute(): string
    {
        $roleNames = [
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'agent' => 'Agent',
            'support' => 'Support Client',
            'client' => 'Client',
        ];

        $role = $this->roles->first();
        return $role ? ($roleNames[$role->name] ?? $role->name) : 'Aucun rôle';
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur
     */
    public function getUserPermissions(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Scope: Filtrer par rôle (colonne role pour compatibilité)
     */
    public function scopeRole($query, string $role)
    {
        return $query->whereHas('roles', function($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Scope: Admins seulement
     */
    public function scopeAdmins($query)
    {
        return $query->role('admin');
    }

    /**
     * Scope: Agents seulement
     */
    public function scopeAgents($query)
    {
        return $query->role('agent');
    }

    /**
     * Scope: Managers seulement
     */
    public function scopeManagers($query)
    {
        return $query->role('manager');
    }

    /**
     * Obtenir le badge de couleur selon le rôle
     */
    public function getRoleBadgeColorAttribute(): string
    {
        $role = $this->roles->first();
        if (!$role) return 'bg-gray-100 text-gray-800';

        return match($role->name) {
            'admin' => 'bg-red-100 text-red-800',
            'manager' => 'bg-blue-100 text-blue-800',
            'agent' => 'bg-green-100 text-green-800',
            'support' => 'bg-yellow-100 text-yellow-800',
            'client' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Vérifier si 2FA est activé
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Vérifier si cet appareil est de confiance
     */
    public function isDeviceTrusted(string $fingerprint): bool
    {
        return $this->devices()
            ->where('device_fingerprint', $fingerprint)
            ->trusted()
            ->exists();
    }
}
