<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeviceFingerprintService
{
    /**
     * Générer l'empreinte unique de l'appareil
     *
     * Combinaison de: User-Agent + IP + Accept-Language
     * Hashé en SHA256 pour anonymat et unicité
     */
    public function generate(Request $request): string
    {
        $components = [
            $request->userAgent() ?? 'unknown',
            $request->ip() ?? 'unknown',
            $request->header('Accept-Language') ?? 'unknown',
        ];

        $fingerprint = implode('|', $components);

        return hash('sha256', $fingerprint);
    }

    /**
     * Obtenir le nom lisible de l'appareil depuis User-Agent
     */
    public function getDeviceName(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (!$userAgent) {
            return 'Appareil inconnu';
        }

        // Détection navigateur
        $browser = 'Navigateur';
        if (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'Opera')) {
            $browser = 'Opera';
        }

        // Détection OS
        $os = '';
        if (str_contains($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac OS')) {
            $os = 'MacOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $os = 'iOS';
        }

        return trim("{$browser} sur {$os}");
    }

    /**
     * Vérifier si l'appareil a changé significativement
     */
    public function hasChanged(string $oldFingerprint, Request $request): bool
    {
        $newFingerprint = $this->generate($request);
        return $oldFingerprint !== $newFingerprint;
    }
}
