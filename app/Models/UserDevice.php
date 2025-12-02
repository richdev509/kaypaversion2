<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'user_agent',
        'ip_address',
        'trusted_at',
        'last_used_at',
    ];

    protected $casts = [
        'trusted_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relation: Appareil appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si l'appareil est de confiance
     */
    public function isTrusted(): bool
    {
        return !is_null($this->trusted_at);
    }

    /**
     * Marquer l'appareil comme de confiance
     */
    public function markAsTrusted(): void
    {
        $this->update([
            'trusted_at' => now(),
            'last_used_at' => now(),
        ]);
    }

    /**
     * Mettre à jour la dernière utilisation
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope: Appareils de confiance uniquement
     */
    public function scopeTrusted($query)
    {
        return $query->whereNotNull('trusted_at');
    }

    /**
     * Scope: Appareils non confirmés
     */
    public function scopeUntrusted($query)
    {
        return $query->whereNull('trusted_at');
    }

    /**
     * Scope: Appareils utilisés récemment (derniers 30 jours)
     */
    public function scopeRecentlyUsed($query, int $days = 30)
    {
        return $query->where('last_used_at', '>=', now()->subDays($days));
    }
}
