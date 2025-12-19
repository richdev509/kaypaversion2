<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ✏️ Modifier le Transfert #{{ $transfer->transfer_number }}
            </h2>
            <a href="{{ route('transfers.show', $transfer) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-200">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Avertissement -->
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4 dark:bg-yellow-900 dark:border-yellow-700">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Attention</h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Vous ne pouvez modifier que les informations de base. Les frais seront automatiquement recalculés si vous modifiez le montant.
                            Toutes les modifications seront enregistrées dans l'historique.
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('transfers.update', $transfer) }}">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">

                        <!-- Informations de l'expéditeur -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="fas fa-user"></i> Expéditeur
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nom Complet -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nom Complet <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="sender_name" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value="{{ old('sender_name', $transfer->sender_name) }}">
                                    @error('sender_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Téléphone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Téléphone <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <select name="sender_country_code" required
                                            class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="+509" {{ $transfer->sender_country_code == '+509' ? 'selected' : '' }}>🇭🇹 +509</option>
                                            <option value="+1" {{ $transfer->sender_country_code == '+1' ? 'selected' : '' }}>🇺🇸🇨🇦 +1</option>
                                            <option value="+33" {{ $transfer->sender_country_code == '+33' ? 'selected' : '' }}>🇫🇷 +33</option>
                                            <option value="+590" {{ $transfer->sender_country_code == '+590' ? 'selected' : '' }}>🇬🇵 +590</option>
                                            <option value="+596" {{ $transfer->sender_country_code == '+596' ? 'selected' : '' }}>🇲🇶 +596</option>
                                            <option value="+594" {{ $transfer->sender_country_code == '+594' ? 'selected' : '' }}>🇬🇫 +594</option>
                                            <option value="+1-809" {{ $transfer->sender_country_code == '+1-809' ? 'selected' : '' }}>🇩🇴 +1-809</option>
                                        </select>
                                        <input type="text" name="sender_phone" required
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('sender_phone', $transfer->sender_phone) }}"
                                            placeholder="XX-XX-XXXX">
                                    </div>
                                    @error('sender_phone')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Informations du bénéficiaire -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="fas fa-user-check"></i> Bénéficiaire
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nom Complet -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nom Complet <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="receiver_name" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value="{{ old('receiver_name', $transfer->receiver_name) }}">
                                    @error('receiver_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Téléphone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Téléphone <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <select name="receiver_country_code" required
                                            class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="+509" {{ $transfer->receiver_country_code == '+509' ? 'selected' : '' }}>🇭🇹 +509</option>
                                            <option value="+1" {{ $transfer->receiver_country_code == '+1' ? 'selected' : '' }}>🇺🇸🇨🇦 +1</option>
                                            <option value="+33" {{ $transfer->receiver_country_code == '+33' ? 'selected' : '' }}>🇫🇷 +33</option>
                                            <option value="+590" {{ $transfer->receiver_country_code == '+590' ? 'selected' : '' }}>🇬🇵 +590</option>
                                            <option value="+596" {{ $transfer->receiver_country_code == '+596' ? 'selected' : '' }}>🇲🇶 +596</option>
                                            <option value="+594" {{ $transfer->receiver_country_code == '+594' ? 'selected' : '' }}>🇬🇫 +594</option>
                                            <option value="+1-809" {{ $transfer->receiver_country_code == '+1-809' ? 'selected' : '' }}>🇩🇴 +1-809</option>
                                        </select>
                                        <input type="text" name="receiver_phone" required
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('receiver_phone', $transfer->receiver_phone) }}"
                                            placeholder="XX-XX-XXXX">
                                    </div>
                                    @error('receiver_phone')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Montant -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                <i class="fas fa-money-bill-wave"></i> Montant
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Montant du Transfert (GDS) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="amount" step="0.01" min="500" max="75000" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value="{{ old('amount', $transfer->amount) }}">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Min: 500 GDS - Max: 75,000 GDS
                                    </p>
                                    @error('amount')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Frais actuels:</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($transfer->fees, 2) }} GDS
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Les frais seront recalculés si vous modifiez le montant
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('transfers.show', $transfer) }}"
                                class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit"
                                class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition">
                                <i class="fas fa-save"></i> Enregistrer les Modifications
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
