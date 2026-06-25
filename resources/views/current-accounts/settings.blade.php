<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('current-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                ← Retour
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Paramètres — Compte Courant
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('current-accounts.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Frais d'ouverture -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Frais d'ouverture (GDS)
                            </label>
                            <input type="number" name="cc_frais_ouverture" step="0.01" min="0"
                                value="{{ old('cc_frais_ouverture', $settings['cc_frais_ouverture'] ?? 200) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Montant prélevé en GDS lors de l'ouverture d'un compte courant. Ne s'ajoute pas au solde du compte.
                            </p>
                            @error('cc_frais_ouverture')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Frais de service mensuel -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Frais de service mensuel (HTG)
                            </label>
                            <input type="number" name="cc_frais_service_mensuel" step="0.01" min="0"
                                value="{{ old('cc_frais_service_mensuel', $settings['cc_frais_service_mensuel'] ?? 10) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Montant déduit mensuellement du solde de chaque compte courant actif.
                            </p>
                            @error('cc_frais_service_mensuel')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Activer/Désactiver frais de service -->
                        <div class="mb-8">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="cc_frais_service_actif" value="0">
                                <input type="checkbox" name="cc_frais_service_actif" value="1"
                                    @checked($settings['cc_frais_service_actif'] ?? true)
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Activer le prélèvement automatique des frais de service mensuels
                                </span>
                            </label>
                            <p class="mt-1 ml-7 text-xs text-gray-500 dark:text-gray-400">
                                Si désactivé, la commande <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">php artisan cc:monthly-fees</code> ne prélève rien.
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                                Enregistrer les paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
