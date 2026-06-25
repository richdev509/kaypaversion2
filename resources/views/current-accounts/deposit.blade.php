<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('current-accounts.show', $currentAccount) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                ← Retour
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dépôt — {{ $currentAccount->account_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            <!-- Solde actuel -->
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Client :</strong> {{ $currentAccount->client?->full_name }}<br>
                    <strong>Solde actuel :</strong> {{ number_format($currentAccount->balance, 2) }} HTG
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg">
                        {{ session('error') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('current-accounts.deposit', $currentAccount) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Montant (HTG) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="amount" step="0.01" min="1"
                                value="{{ old('amount') }}" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0.00">
                            @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Mode de paiement <span class="text-red-500">*</span>
                            </label>
                            <select name="method" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                                <option value="">-- Choisir --</option>
                                <option value="cash" @selected(old('method') == 'cash')>Espèces (Cash)</option>
                                <option value="moncash" @selected(old('method') == 'moncash')>MonCash</option>
                                <option value="bank_transfer" @selected(old('method') == 'bank_transfer')>Virement bancaire</option>
                            </select>
                            @error('method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Note (optionnel)</label>
                            <textarea name="note" rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Remarque...">{{ old('note') }}</textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('current-accounts.show', $currentAccount) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm">
                                Annuler
                            </a>
                            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                                Confirmer le dépôt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
