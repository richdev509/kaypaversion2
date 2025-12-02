<?php

namespace App\Http\Controllers;

use App\Models\UserDevice;
use App\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorController extends Controller
{
    protected $google2fa;
    protected $deviceService;

    public function __construct(DeviceFingerprintService $deviceService)
    {
        $this->google2fa = new Google2FA();
        $this->deviceService = $deviceService;
    }

    /**
     * Afficher la page de configuration 2FA (génération QR Code)
     */
    public function enable(Request $request)
    {
        $user = Auth::user();

        // Si déjà activé, rediriger
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard')->with('info', '2FA déjà activé sur votre compte.');
        }

        // Générer secret Base32 (32 caractères)
        $secret = $this->google2fa->generateSecretKey();

        // Validation format Base32
        if (!preg_match('/^[A-Z2-7]+$/', $secret)) {
            Log::error('2FA: Secret généré invalide', ['secret' => $secret]);
            return back()->with('error', 'Erreur génération secret 2FA');
        }

        // Stocker temporairement en session
        $request->session()->put('2fa_setup_secret', $secret);

        Log::info('2FA Setup: Secret généré', [
            'user_id' => $user->id,
            'secret_length' => strlen($secret),
        ]);

        // Créer URL TOTP standard (CRITIQUE)
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'KAYPA'),
            $user->email,
            $secret
        );

        // Ajouter paramètres explicites (conformité RFC 6238)
        $qrCodeUrl .= '&algorithm=SHA1&digits=6&period=30';

        // Générer QR Code avec SimpleSoftwareIO
        $qrCodeSvg = QrCode::size(300)
            ->errorCorrection('H')
            ->generate($qrCodeUrl);

        return view('auth.two-factor.enable', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret,
        ]);
    }

    /**
     * Confirmer l'activation 2FA (validation code + génération codes récupération)
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $user = Auth::user();
        $secret = $request->session()->get('2fa_setup_secret');

        if (!$secret) {
            return back()->with('error', 'Session expirée. Recommencez la configuration.');
        }

        // Vérification code avec TOLÉRANCE ±5 minutes (10 intervalles * 30sec)
        $isValid = $this->google2fa->verifyKey($secret, $request->code, 10);

        Log::info('2FA Confirm: Tentative validation', [
            'user_id' => $user->id,
            'code_entered' => $request->code,
            'valid' => $isValid,
        ]);

        if (!$isValid) {
            return back()->with('error', 'Code incorrect. Vérifiez l\'heure de votre téléphone.');
        }

        // Générer 8 codes de récupération
        $recoveryCodes = $this->generateRecoveryCodes();

        // Sauvegarder en BD (chiffré)
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes['hashed'])),
            'two_factor_confirmed_at' => now(),
        ]);

        // Enregistrer l'appareil actuel comme de confiance automatiquement
        $fingerprint = $this->deviceService->generate($request);
        $deviceName = $this->deviceService->getDeviceName($request);

        UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
            ],
            [
                'device_name' => $deviceName,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'trusted_at' => now(),
                'last_used_at' => now(),
            ]
        );

        // Nettoyer session
        $request->session()->forget('2fa_setup_secret');

        Log::info('2FA Confirm: Activé avec succès', ['user_id' => $user->id]);

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $recoveryCodes['plain'],
        ]);
    }

    /**
     * Afficher formulaire vérification 2FA
     */
    public function show(Request $request)
    {
        // Récupérer ID utilisateur depuis session
        $userId = $request->session()->get('2fa:auth:id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expirée.');
        }

        return view('auth.two-factor.verify');
    }

    /**
     * Vérifier code 2FA lors de la connexion
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:6|max:10',
        ]);

        $userId = $request->session()->get('2fa:auth:id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expirée.');
        }

        $user = \App\Models\User::findOrFail($userId);

        // Essayer d'abord avec code TOTP
        $secret = decrypt($user->two_factor_secret);
        $isValid = $this->google2fa->verifyKey($secret, $request->code, 10);

        if ($isValid) {
            $this->loginUserAndTrustDevice($request, $user);
            return redirect()->intended(route('dashboard'));
        }

        // Essayer avec code de récupération
        if ($this->verifyRecoveryCode($user, $request->code)) {
            $this->loginUserAndTrustDevice($request, $user);
            return redirect()->intended(route('dashboard'))
                ->with('warning', 'Code de récupération utilisé. Pensez à en régénérer.');
        }

        Log::warning('2FA Verify: Code invalide', [
            'user_id' => $user->id,
            'code' => $request->code,
        ]);

        return back()->with('error', 'Code incorrect.');
    }

    /**
     * Désactiver 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        Log::info('2FA: Désactivé', ['user_id' => $user->id]);

        return redirect()->route('dashboard')->with('success', '2FA désactivé avec succès.');
    }

    /**
     * Afficher codes de récupération
     */
    public function showRecoveryCodes()
    {
        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard')->with('error', '2FA non activé.');
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        // Afficher seulement les codes non utilisés (non-null)
        $availableCodes = array_filter($recoveryCodes, fn($code) => !is_null($code));

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => array_values($availableCodes),
        ]);
    }

    /**
     * Régénérer nouveaux codes de récupération
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();

        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard')->with('error', '2FA non activé.');
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes['hashed'])),
        ]);

        Log::info('2FA: Codes récupération régénérés', ['user_id' => $user->id]);

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $recoveryCodes['plain'],
        ])->with('success', 'Nouveaux codes générés. Sauvegardez-les maintenant !');
    }

    /**
     * Générer 8 codes de récupération
     */
    protected function generateRecoveryCodes(): array
    {
        $plainCodes = [];
        $hashedCodes = [];

        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(Str::random(10));
            $plainCodes[] = $code;
            $hashedCodes[] = hash('sha256', $code);
        }

        return [
            'plain' => $plainCodes,
            'hashed' => $hashedCodes,
        ];
    }

    /**
     * Vérifier code de récupération
     */
    protected function verifyRecoveryCode($user, string $code): bool
    {
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $hashedCode = hash('sha256', $code);

        $index = array_search($hashedCode, $recoveryCodes);

        if ($index !== false) {
            // Marquer comme utilisé (remplacer par null)
            $recoveryCodes[$index] = null;
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            ]);

            Log::info('2FA: Code récupération utilisé', [
                'user_id' => $user->id,
                'index' => $index,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Connecter l'utilisateur et enregistrer l'appareil
     */
    protected function loginUserAndTrustDevice(Request $request, $user): void
    {
        // Connexion
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('2fa:auth:id');

        // Enregistrer appareil comme de confiance
        $fingerprint = $this->deviceService->generate($request);
        $deviceName = $this->deviceService->getDeviceName($request);

        UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
            ],
            [
                'device_name' => $deviceName,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'trusted_at' => now(),
                'last_used_at' => now(),
            ]
        );

        Log::info('2FA: Connexion réussie', [
            'user_id' => $user->id,
            'device' => $deviceName,
        ]);
    }

    /**
     * Retirer un appareil de confiance
     */
    public function removeDevice(Request $request, UserDevice $device)
    {
        $user = Auth::user();

        // Vérifier que l'appareil appartient à l'utilisateur
        if ($device->user_id !== $user->id) {
            return back()->with('error', 'Appareil non trouvé.');
        }

        $deviceName = $device->device_name;
        $device->delete();

        Log::info('2FA: Appareil retiré', [
            'user_id' => $user->id,
            'device' => $deviceName,
        ]);

        return back()->with('success', "Appareil \"{$deviceName}\" retiré. Code 2FA sera demandé lors de la prochaine connexion depuis cet appareil.");
    }
}
