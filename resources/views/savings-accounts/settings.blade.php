<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('savings-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">← Retour</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Paramètres — Compte Épargne</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('savings-accounts.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Frais d'ouverture (HTG)
                            <span class="text-xs font-normal text-gray-400 ml-1">— 0 = gratuit</span>
                        </label>
                        <input type="number" name="sce_frais_ouverture" min="0" step="0.01"
                            value="{{ old('sce_frais_ouverture', $settings['sce_frais_ouverture'] ?? 0) }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Taux d'intérêt mensuel (%)
                        </label>
                        <input type="number" name="sce_taux_interet_mensuel" min="0" max="100" step="0.01"
                            value="{{ old('sce_taux_interet_mensuel', $settings['sce_taux_interet_mensuel'] ?? 0.5) }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ex : 0.5 = 0.5% par mois sur le solde fin de mois</p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Solde minimum obligatoire (GDS)
                            <span class="text-xs font-normal text-gray-400 ml-1">— dépôt initial + plancher de retrait</span>
                        </label>
                        <input type="number" name="sce_solde_minimum" min="0" step="1"
                            value="{{ old('sce_solde_minimum', $settings['sce_solde_minimum'] ?? 500) }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Solde minimum pour bénéficier des intérêts (GDS)
                        </label>
                        <input type="number" name="sce_solde_minimum_interet" min="0" step="1"
                            value="{{ old('sce_solde_minimum_interet', $settings['sce_solde_minimum_interet'] ?? 500) }}"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="sce_interet_actif" value="1"
                                @checked(($settings['sce_interet_actif'] ?? true) === true)
                                class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Activer le versement automatique des intérêts (1er de chaque mois)
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium">
                            Enregistrer les paramètres
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
