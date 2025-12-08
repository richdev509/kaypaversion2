<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Codes de Récupération 2FA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Message de succès -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                        <p class="text-green-800 dark:text-green-200 font-semibold">
                            ✅ Authentification à deux facteurs activée avec succès !
                        </p>
                    </div>

                    <!-- Avertissement important -->
                    <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-lg p-4 mb-6">
                        <p class="text-red-800 dark:text-red-200 font-bold mb-2">
                            ⚠️ IMPORTANT : Sauvegardez ces codes de récupération
                        </p>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            Ces codes vous permettront d'accéder à votre compte si vous perdez votre téléphone.
                            Chaque code ne peut être utilisé qu'une seule fois.
                            <strong>Conservez-les dans un endroit sûr !</strong>
                        </p>
                    </div>

                    <!-- Liste des codes -->
                    <div class="mb-6">
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-6">
                            <h3 class="font-semibold text-lg mb-4 text-center">Vos Codes de Récupération</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($recoveryCodes as $index => $code)
                                    <div class="bg-white dark:bg-gray-800 p-3 rounded border border-gray-300 dark:border-gray-600 text-center">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Code {{ $index + 1 }}</span>
                                        <p class="font-mono text-lg font-bold text-gray-900 dark:text-white">
                                            {{ $code }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button onclick="printCodes()" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                            🖨️ Imprimer les codes
                        </button>
                        <button onclick="copyCodes()" class="flex-1 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition">
                            📋 Copier les codes
                        </button>
                        <a href="{{ route('dashboard') }}" class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-center transition">
                            ✅ J'ai sauvegardé les codes
                        </a>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                        <p class="font-semibold mb-2">Comment utiliser ces codes :</p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li>Utilisez un code de récupération si vous n'avez pas accès à Google Authenticator</li>
                            <li>Chaque code ne fonctionne qu'une seule fois</li>
                            <li>Vous pouvez régénérer de nouveaux codes à tout moment depuis les paramètres</li>
                            <li>Ne partagez jamais ces codes avec qui que ce soit</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Zone cachée pour impression -->
    <div id="printArea" class="hidden">
        <h1 style="text-align: center; margin-bottom: 20px;">Codes de Récupération 2FA - KAYPA</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Compte: {{ Auth::user()->email }}<br>
            Générés le: {{ now()->format('d/m/Y à H:i') }}
        </p>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; max-width: 600px; margin: 0 auto;">
            @foreach($recoveryCodes as $index => $code)
                <div style="border: 2px solid #333; padding: 15px; text-align: center; border-radius: 8px;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Code {{ $index + 1 }}</div>
                    <div style="font-family: monospace; font-size: 18px; font-weight: bold;">{{ $code }}</div>
                </div>
            @endforeach
        </div>
        <p style="margin-top: 40px; text-align: center; color: #d32f2f; font-weight: bold;">
            ⚠️ Conservez ces codes dans un endroit sûr !
        </p>
    </div>

    <script>
        function copyCodes() {
            const codes = @json($recoveryCodes);
            const text = codes.join('\n');

            navigator.clipboard.writeText(text).then(() => {
                alert('✅ Codes copiés dans le presse-papiers !');
            }).catch(() => {
                alert('❌ Erreur lors de la copie. Copiez manuellement.');
            });
        }

        function printCodes() {
            const printContent = document.getElementById('printArea').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = printContent;
            window.print();

            // Rediriger vers le dashboard au lieu de recharger
            window.location.href = '{{ route("dashboard") }}';
        }
    </script>
</x-app-layout>
