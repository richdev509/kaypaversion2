<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\DeviceFingerprintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceFingerprintService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Si 2FA activé, vérifier l'appareil
        if ($user->hasTwoFactorEnabled()) {
            $fingerprint = $this->deviceService->generate($request);

            // Vérifier si appareil de confiance
            if ($user->isDeviceTrusted($fingerprint)) {
                // Appareil connu → Connexion directe
                $request->session()->regenerate();

                // Mettre à jour last_used_at
                $device = $user->devices()->where('device_fingerprint', $fingerprint)->first();
                if ($device) {
                    $device->updateLastUsed();
                }

                Log::info('2FA: Connexion depuis appareil de confiance', [
                    'user_id' => $user->id,
                    'device' => $this->deviceService->getDeviceName($request),
                ]);

                return redirect()->intended(route('dashboard', absolute: false));
            }

            // Nouvel appareil → Demander 2FA
            Log::info('2FA: Nouvel appareil détecté au login', [
                'user_id' => $user->id,
                'device' => $this->deviceService->getDeviceName($request),
                'ip' => $request->ip(),
            ]);

            // Stocker ID pour vérification 2FA
            $request->session()->put('2fa:auth:id', $user->id);

            // Déconnecter temporairement
            Auth::logout();

            return redirect()->route('two-factor.show')
                ->with('warning', 'Nouvel appareil détecté. Veuillez vérifier votre identité.');
        }

        // Pas de 2FA, connexion normale
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
