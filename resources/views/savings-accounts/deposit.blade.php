<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('savings-accounts.show', $savingsAccount) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">← Retour</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dépôt — {{ $savingsAccount->account_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">

                <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ $savingsAccount->client?->full_name }}</p>
                        <p class="font-mono text-xs text-emerald-600 dark:text-emerald-400">{{ $savingsAccount->account_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">Solde actuel</p>
                        <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($savingsAccount->balance, 2) }} HTG</p>
                    </div>
                </div>

                @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('savings-accounts.deposit', $savingsAccount) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Montant (HTG) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount') }}" min="1" step="0.01" required
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 @error('amount') border-red-500 @enderror">
                        @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode de paiement <span class="text-red-500">*</span></label>
                        <select name="method" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Choisir --</option>
                            <option value="cash"          @selected(old('method') == 'cash')>Espèces (Cash)</option>
                            <option value="moncash"       @selected(old('method') == 'moncash')>MonCash</option>
                            <option value="bank_transfer" @selected(old('method') == 'bank_transfer')>Virement bancaire</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Note (optionnel)</label>
                        <textarea name="note" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('note') }}</textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('savings-accounts.show', $savingsAccount) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm">Annuler</a>
                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">Confirmer le dépôt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
