<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ⚙️ {{ __('Paramètres des Transferts') }}
            </h2>
            <a href="{{ route('transfers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-6 bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                <p class="font-semibold mb-2"><i class="fas fa-exclamation-triangle"></i> Erreurs de validation :</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-500 to-blue-600">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-cog"></i> Configuration des Transferts
                    </h3>
                    <p class="text-sm text-blue-100 mt-1">Gérez les montants, frais et réductions pour les transferts</p>
                </div>

                <form method="POST" action="{{ route('transfers.settings.update') }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Montants limites -->
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-coins text-yellow-500"></i> Montants Limites
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Montant Minimum (GDS) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="min_amount" value="{{ old('min_amount', $settings->min_amount) }}"
                                    step="0.01" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Montant minimum accepté pour un transfert</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Montant Maximum (GDS) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="max_amount" value="{{ old('max_amount', $settings->max_amount) }}"
                                    step="0.01" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Montant maximum accepté pour un transfert</p>
                            </div>
                        </div>
                    </div>

                    <!-- Frais de transfert -->
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-percent text-green-500"></i> Frais de Transfert
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Frais en Pourcentage (%) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="transfer_fee_percentage" value="{{ old('transfer_fee_percentage', $settings->transfer_fee_percentage) }}"
                                    step="0.01" min="0" max="100" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pourcentage appliqué sur le montant (ex: 2 pour 2%)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Frais Fixes (GDS) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="transfer_fee_fixed" value="{{ old('transfer_fee_fixed', $settings->transfer_fee_fixed) }}"
                                    step="0.01" min="0" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Montant fixe ajouté aux frais (ex: 100 GDS)</p>
                            </div>
                        </div>

                        <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <i class="fas fa-info-circle"></i>
                                <strong>Calcul des frais :</strong> (Montant × Pourcentage / 100) + Frais fixes
                            </p>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-2">
                                Exemple avec montant de 1000 GDS : (1000 × {{ $settings->transfer_fee_percentage }} / 100) + {{ $settings->transfer_fee_fixed }} =
                                <strong>{{ number_format((1000 * $settings->transfer_fee_percentage / 100) + $settings->transfer_fee_fixed, 2) }} GDS</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Réduction client Kaypa -->
                    <div class="pb-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-gift text-purple-500"></i> Réduction Client Kaypa
                        </h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Réduction en Pourcentage (%) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="kaypa_client_discount" value="{{ old('kaypa_client_discount', $settings->kaypa_client_discount) }}"
                                step="0.01" min="0" max="100" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Réduction sur les frais pour les clients ayant un compte Kaypa
                            </p>
                        </div>

                        <div class="mt-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                            <p class="text-sm text-purple-800 dark:text-purple-200">
                                <i class="fas fa-star"></i>
                                <strong>Avantage client Kaypa :</strong> Réduction de {{ $settings->kaypa_client_discount }}% sur les frais de transfert
                            </p>
                            <p class="text-sm text-purple-700 dark:text-purple-300 mt-2">
                                @php
                                    $exampleFees = (1000 * $settings->transfer_fee_percentage / 100) + $settings->transfer_fee_fixed;
                                    $exampleDiscount = $exampleFees * $settings->kaypa_client_discount / 100;
                                    $exampleFinal = $exampleFees - $exampleDiscount;
                                @endphp
                                Exemple : Frais de {{ number_format($exampleFees, 2) }} GDS - Réduction de {{ number_format($exampleDiscount, 2) }} GDS =
                                <strong>{{ number_format($exampleFinal, 2) }} GDS</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('transfers.index') }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit"
                            class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200">
                            <i class="fas fa-save"></i> Enregistrer les Paramètres
                        </button>
                    </div>
                </form>
            </div>

            <!-- Aperçu des paramètres actuels -->
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-500 to-gray-600">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-eye"></i> Aperçu des Paramètres Actuels
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Montants acceptés</p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format($settings->min_amount, 0) }} - {{ number_format($settings->max_amount, 0) }} GDS
                            </p>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Frais de transfert</p>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                @if($settings->transfer_fee_percentage > 0)
                                    {{ $settings->transfer_fee_percentage }}%
                                    @if($settings->transfer_fee_fixed > 0)
                                        + {{ number_format($settings->transfer_fee_fixed, 0) }} GDS
                                    @endif
                                @else
                                    {{ number_format($settings->transfer_fee_fixed, 0) }} GDS
                                @endif
                            </p>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Réduction Kaypa</p>
                            <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                {{ $settings->kaypa_client_discount }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
