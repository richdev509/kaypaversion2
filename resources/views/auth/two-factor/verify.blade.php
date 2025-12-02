<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Nouvel appareil détecté. Veuillez entrer votre code Google Authenticator pour continuer.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <!-- Code 2FA -->
        <div>
            <x-input-label for="code" :value="__('Code à 6 chiffres')" />
            <x-text-input
                id="code"
                class="block mt-1 w-full text-center text-2xl font-bold tracking-widest"
                type="text"
                name="code"
                inputmode="numeric"
                maxlength="10"
                pattern="[0-9A-Z]{6,10}"
                autocomplete="one-time-code"
                required
                autofocus
                placeholder="000000"
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />

            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Entrez le code affiché dans Google Authenticator ou un code de récupération
            </p>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mt-4">
            <p class="text-xs text-blue-800 dark:text-blue-200">
                💡 <strong>Astuce :</strong> Ce code change toutes les 30 secondes. Si le code ne fonctionne pas, attendez le prochain.
            </p>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="w-full justify-center">
                {{ __('Vérifier') }}
            </x-primary-button>
        </div>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline">
                    Annuler et se déconnecter
                </button>
            </form>
        </div>
    </form>

    <script>
        const codeInput = document.getElementById('code');

        codeInput.addEventListener('input', function(e) {
            // Accepter chiffres et lettres majuscules (codes récupération)
            this.value = this.value.replace(/[^0-9A-Z]/gi, '').toUpperCase();

            // Auto-submit si 6 chiffres (TOTP)
            if (this.value.length === 6 && /^[0-9]{6}$/.test(this.value)) {
                setTimeout(() => this.form.submit(), 100);
            }
            // Auto-submit si 10 caractères (code récupération)
            if (this.value.length === 10) {
                setTimeout(() => this.form.submit(), 100);
            }
        });
    </script>
</x-guest-layout>
