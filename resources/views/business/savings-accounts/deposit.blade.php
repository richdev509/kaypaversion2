<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.keb.show', $account) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dépôt KEB — {{ $account->business->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $account->account_number }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">

            {{-- Alerte business non opérationnel --}}
            @if($account->business->status !== 'active' || $account->business->status_kyc !== 'verified')
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg">
                <p class="text-sm font-medium text-red-700 dark:text-red-400">
                    Opération impossible — ce business
                    @if($account->business->status_kyc !== 'verified')
                        n'a pas de KYC approuvé.
                    @else
                        est suspendu ou clôturé.
                    @endif
                </p>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">

                <div class="mb-5 p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Solde actuel</span>
                    <span class="font-semibold text-indigo-700 dark:text-indigo-300">{{ number_format($account->balance, 2) }} HTG</span>
                </div>

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-sm text-red-700 dark:text-red-400">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('business.keb.deposit', $account) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Montant (HTG) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount" value="{{ old('amount') }}"
                            min="1" step="0.01" required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
                        <textarea name="note" rows="2"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('business.keb.show', $account) }}"
                           class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Annuler
                        </a>
                        <button type="submit"
                            @if($account->business->status !== 'active' || $account->business->status_kyc !== 'verified') disabled @endif
                            class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed">
                            Confirmer le dépôt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
