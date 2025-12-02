<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Activer l\'Authentification à Deux Facteurs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Instructions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">🔐 Configuration de l'authentification à deux facteurs</h3>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Pourquoi activer 2FA ?</strong><br>
                                La 2FA protège votre compte même si quelqu'un connaît votre mot de passe.
                                Elle sera demandée uniquement lors de la connexion depuis un nouvel appareil.
                            </p>
                        </div>

                        <div class="space-y-2 text-sm mb-6">
                            <p class="font-semibold">Étapes d'activation :</p>
                            <ol class="list-decimal list-inside space-y-2 ml-2">
                                <li>Installez <strong>Google Authenticator</strong> sur votre téléphone :
                                    <div class="flex gap-2 mt-2 ml-6">
                                        <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="text-blue-600 hover:underline">
                                            📱 iOS (App Store)
                                        </a>
                                        <span>|</span>
                                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="text-blue-600 hover:underline">
                                            🤖 Android (Play Store)
                                        </a>
                                    </div>
                                </li>
                                <li>Scannez le QR Code ci-dessous avec l'application</li>
                                <li>Entrez le code à 6 chiffres généré pour confirmer</li>
                                <li>Sauvegardez les codes de récupération</li>
                            </ol>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="text-center mb-8">
                        <div class="inline-block p-6 bg-white rounded-lg shadow-md">
                            <h4 class="font-semibold text-gray-900 mb-4">Scannez ce QR Code</h4>
                            <div class="mb-4">
                                {!! $qrCodeSvg !!}
                            </div>
                            <div class="mt-4 p-3 bg-gray-100 rounded text-xs font-mono break-all">
                                <p class="text-gray-600 mb-1">Clé secrète manuelle :</p>
                                <p class="font-bold text-gray-900">{{ $secret }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Si vous ne pouvez pas scanner le QR code, entrez manuellement cette clé dans Google Authenticator
                            </p>
                        </div>
                    </div>

                    <!-- Formulaire de confirmation -->
                    <div class="max-w-md mx-auto">
                        <form method="POST" action="{{ route('two-factor.confirm') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="code" class="block text-sm font-medium mb-2">
                                    Code de vérification (6 chiffres)
                                </label>
                                <input
                                    type="text"
                                    id="code"
                                    name="code"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    required
                                    class="w-full px-4 py-3 text-center text-2xl font-bold border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="000000"
                                    autofocus
                                >
                                @error('code')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
                                <p class="text-xs text-yellow-800 dark:text-yellow-200">
                                    ⚠️ <strong>Important :</strong> Assurez-vous que l'heure de votre téléphone est correcte.
                                    Un décalage horaire peut causer des codes invalides.
                                </p>
                            </div>

                            <div class="flex gap-4">
                                <button type="submit" class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                                    ✅ Activer 2FA
                                </button>
                                <a href="{{ route('dashboard') }}" class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg text-center transition">
                                    Annuler
                                </a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus et validation format
        const codeInput = document.getElementById('code');

        codeInput.addEventListener('input', function(e) {
            // Garder seulement chiffres
            this.value = this.value.replace(/[^0-9]/g, '');

            // Auto-submit si 6 chiffres
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</x-app-layout>
