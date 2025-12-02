<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TokenService
{
    /**
     * Stocker un token en cache (expiration: 3 minutes)
     */
    public function storeToken(string $token): void
    {
        Cache::put("scan_token_{$token}", true, now()->addMinutes(3));
    }

    /**
     * Vérifier si un token est valide
     *
     * @return int  1 si valide, -1 si expiré
     */
    public function verifyOrCreate(string $token): int
    {
        if (Cache::has("scan_token_{$token}")) {
            return 1; // Token valide
        }

        return -1; // Token expiré
    }

    /**
     * Supprimer un token du cache
     */
    public function deleteToken(string $token): void
    {
        Cache::forget("scan_token_{$token}");
    }
}
