<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.kcb.show', $account) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Retrait — <span class="font-mono">{{ $account->account_number }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">

                <div class="mb-5 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $account->business->name }}</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                        {{ number_format($account->balance, 2) }} HTG <span class="text-sm font-normal text-gray-500">disponible</span>
                    </p>
                </div>

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('business.kcb.withdraw', $account) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Montant (HTG) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount" value="{{ old('amount') }}"
                            required min="1" step="0.01" max="{{ $account->balance }}"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-red-500 focus:border-red-500 text-xl font-semibold">
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Méthode <span class="text-red-500">*</span>
                        </label>
                        <select name="method" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-red-500 focus:border-red-500">
                            <option value="especes" @selected(old('method') === 'especes')>Espèces</option>
                            <option value="cheque" @selected(old('method') === 'cheque')>Chèque</option>
                            <option value="virement" @selected(old('method') === 'virement')>Virement</option>
                        </select>
                        @error('method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Référence</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" maxlength="100"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
                        <textarea name="note" rows="2" maxlength="500"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-red-500 focus:border-red-500">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('business.kcb.show', $account) }}"
                           class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Annuler
                        </a>
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg transition font-semibold">
                            Confirmer le retrait
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
