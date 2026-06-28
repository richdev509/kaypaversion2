<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('school-programs.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Nouveau Programme Scolaire</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('school-programs.store') }}" class="space-y-6">
                @csrf

                @if($errors->any())
                <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                    <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Infos générales --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Informations générales</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nom du programme <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="3" maxlength="2000"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Début de validité coupon <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_debut" value="{{ old('date_debut') }}" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fin de validité coupon <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_fin" value="{{ old('date_fin') }}" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Début des inscriptions <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="inscription_debut" value="{{ old('inscription_debut') }}" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fin des inscriptions <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="inscription_fin" value="{{ old('inscription_fin') }}" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Configuration blocage et tiers --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Configuration des montants</h3>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Montant bloqué (GDS) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="montant_blocage" value="{{ old('montant_blocage', 2000) }}" required min="1" step="0.01"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">Montant bloqué sur le KCE du client pendant la durée du programme</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Durée de blocage (jours) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="duree_blocage_jours" value="{{ old('duree_blocage_jours', 90) }}" required min="1" max="365"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">Tier 1 (coupon standard)</p>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Solde épargne minimum (GDS) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="tier1_seuil" value="{{ old('tier1_seuil', 2000) }}" required min="1" step="0.01"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Valeur coupon (GDS) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="tier1_coupon" value="{{ old('tier1_coupon', 500) }}" required min="1" step="0.01"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">Tier 2 (coupon premium)</p>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Solde épargne minimum (GDS) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="tier2_seuil" value="{{ old('tier2_seuil', 10000) }}" required min="1" step="0.01"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Valeur coupon (GDS) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="tier2_coupon" value="{{ old('tier2_coupon', 1000) }}" required min="1" step="0.01"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    {{-- Champ caché pour solde_minimum_epargne = tier1_seuil --}}
                    <input type="hidden" name="solde_minimum_epargne" id="solde_minimum_epargne">
                    <script>
                        document.querySelector('[name="tier1_seuil"]').addEventListener('input', function() {
                            document.getElementById('solde_minimum_epargne').value = this.value;
                        });
                        document.getElementById('solde_minimum_epargne').value = document.querySelector('[name="tier1_seuil"]').value;
                    </script>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('school-programs.index') }}"
                       class="px-5 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium">
                        Créer le programme
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
