<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            🔐 Authentification à Deux Facteurs (2FA)
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Protégez votre compte avec Google Authenticator. La 2FA sera demandée uniquement lors de connexions depuis de nouveaux appareils.
        </p>
    </header>

    @if(Auth::user()->hasTwoFactorEnabled())
        <!-- 2FA Activé -->
        <div class="mt-6 space-y-4">
            <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-500 dark:border-green-700 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold text-green-800 dark:text-green-200">
                            2FA Activée
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Activée le {{ Auth::user()->two_factor_confirmed_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Appareils de confiance -->
            @php
                $trustedDevices = Auth::user()->trustedDevices()->orderBy('last_used_at', 'desc')->get();
            @endphp

            @if($trustedDevices->count() > 0)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">
                        📱 Appareils de confiance ({{ $trustedDevices->count() }})
                    </h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($trustedDevices as $device)
                            <div class="bg-white dark:bg-gray-800 p-3 rounded border border-blue-200 dark:border-gray-600">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $device->device_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            IP: {{ $device->ip_address }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Dernière utilisation: {{ $device->last_used_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('two-factor.device.remove', $device->id) }}" class="ml-2">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            onclick="return confirm('Retirer cet appareil de confiance ? Vous devrez entrer le code 2FA lors de la prochaine connexion depuis cet appareil.')"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-xs">
                                            Retirer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('two-factor.recovery-codes') }}" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center rounded-lg transition">
                    🔑 Voir codes de récupération
                </a>

                <form method="POST" action="{{ route('two-factor.recovery-codes.regenerate') }}" class="flex-1">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Régénérer de nouveaux codes ? Les anciens codes seront invalidés.')"
                        class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        🔄 Régénérer codes
                    </button>
                </form>

                <form method="POST" action="{{ route('two-factor.disable') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="password" id="disable-password" value="">
                    <button
                        type="button"
                        onclick="disableTwoFactor()"
                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        ❌ Désactiver 2FA
                    </button>
                </form>
            </div>
        </div>

    @else
        <!-- 2FA Désactivée -->
        <div class="mt-6 space-y-4">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-yellow-800 dark:text-yellow-200">
                            2FA Non Activée
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Votre compte n'est protégé que par votre mot de passe
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">
                    Pourquoi activer la 2FA ?
                </h3>
                <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Protège votre compte même si votre mot de passe est compromis</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Demandée uniquement sur nouveaux appareils (pas à chaque connexion)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Utilise Google Authenticator (gratuit, pas besoin de SMS)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Codes de récupération fournis en cas de perte du téléphone</span>
                    </li>
                </ul>
            </div>

            <a href="{{ route('two-factor.enable') }}" class="block w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium text-center rounded-lg transition">
                🔒 Activer l'Authentification à Deux Facteurs
            </a>
        </div>
    @endif

    <script>
        function disableTwoFactor() {
            const password = prompt('Entrez votre mot de passe pour confirmer la désactivation de 2FA :');

            if (password) {
                document.getElementById('disable-password').value = password;
                event.target.closest('form').submit();
            }
        }
    </script>
</section>
