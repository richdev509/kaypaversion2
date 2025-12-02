<?php

namespace App\Http\Middleware;

use App\Services\DeviceFingerprintService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckDeviceAnd2FA
{
    protected $deviceService;

    public function __construct(DeviceFingerprintService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Middleware: Vérifier appareil et demander 2FA si nouveau
     *
     * Logique:
     * 1. Si utilisateur n'a pas 2FA activé → Passer
     * 2. Si appareil est de confiance → Passer
     * 3. Si nouvel appareil → Rediriger vers vérification 2FA
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si utilisateur connecté
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si 2FA pas activé, passer
        if (!$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // Exceptions: Routes 2FA elles-mêmes (éviter boucle infinie)
        $exceptRoutes = [
            'two-factor.show',
            'two-factor.verify',
            'two-factor.enable',
            'two-factor.confirm',
            'two-factor.disable',
            'logout',
        ];

        if ($request->routeIs($exceptRoutes)) {
            return $next($request);
        }

        // Générer empreinte appareil actuel
        $fingerprint = $this->deviceService->generate($request);

        // Vérifier si appareil est de confiance
        if ($user->isDeviceTrusted($fingerprint)) {
            // Mettre à jour last_used_at
            $device = $user->devices()->where('device_fingerprint', $fingerprint)->first();
            if ($device) {
                $device->updateLastUsed();
            }

            return $next($request);
        }

        // Nouvel appareil détecté → Déconnecter et demander 2FA
        Log::warning('2FA: Nouvel appareil détecté', [
            'user_id' => $user->id,
            'fingerprint' => substr($fingerprint, 0, 16) . '...',
            'ip' => $request->ip(),
            'device' => $this->deviceService->getDeviceName($request),
        ]);

        // Stocker ID utilisateur en session pour vérification 2FA
        $request->session()->put('2fa:auth:id', $user->id);

        // Déconnecter temporairement
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Restaurer ID pour vérification 2FA
        $request->session()->put('2fa:auth:id', $user->id);

        return redirect()->route('two-factor.show')
            ->with('warning', 'Nouvel appareil détecté. Veuillez vérifier votre identité avec Google Authenticator.');
    }
}
