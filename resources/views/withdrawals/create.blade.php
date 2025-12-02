<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💸 Effectuer un retrait
            </h2>
            <a href="{{ route('accounts.show', $account) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour au compte
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Informations du compte -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Informations du compte
                    </h3>
                    <div class="p-5 bg-orange-600 dark:bg-orange-700 rounded-lg shadow-lg">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-orange-100 dark:text-orange-200 text-xs font-medium mb-1">Compte:</p>
                                <p class="font-bold text-white text-lg">{{ $account->account_id }}</p>
                            </div>
                            <div>
                                <p class="text-orange-100 dark:text-orange-200 text-xs font-medium mb-1">Client:</p>
                                <p class="font-bold text-white text-lg">{{ $account->client->full_name }}</p>
                            </div>
                            <div>
                                <p class="text-orange-100 dark:text-orange-200 text-xs font-medium mb-1">Solde disponible:</p>
                                <p class="font-bold text-white text-xl">{{ number_format($account->getAvailableForWithdrawal(), 2) }} HTG</p>
                            </div>
                            <div>
                                <p class="text-orange-100 dark:text-orange-200 text-xs font-medium mb-1">Date fin plan:</p>
                                <p class="font-bold text-white text-xl">{{ $account->date_fin->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if(!$account->isPlanCompleted())
                        <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-800">
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                ℹ️ Information: Plan non terminé ({{ $account->getDaysRemaining() }} jours restants)
                            </p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-300 mt-1">
                                Un retrait total clôturera le compte définitivement.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Formulaire de retrait -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                        Détails du retrait
                    </h3>

                    <form action="{{ route('withdrawals.store', $account) }}" method="POST">
                        @csrf

                        <!-- Montant -->
                        <div class="mb-6">
                            <label for="withdrawal_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Montant <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    id="withdrawal_amount"
                                    name="amount"
                                    step="0.01"
                                    min="1"
                                    max="{{ $account->getAvailableForWithdrawal() }}"
                                    required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 pr-16 text-lg"
                                    oninput="calculateWithdrawal()"
                                >
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium">HTG</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Maximum: {{ number_format($account->getAvailableForWithdrawal(), 2) }} HTG
                            </p>
                            <p id="withdrawal-info" class="mt-2 text-sm font-medium"></p>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Méthode de retrait -->
                        <div class="mb-6">
                            <label for="withdrawal_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Méthode de retrait <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="withdrawal_method"
                                name="method"
                                required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="">-- Sélectionner --</option>
                                <option value="cash" {{ old('method') == 'cash' ? 'selected' : '' }}>💵 Espèces (Cash)</option>
                                <option value="moncash" {{ old('method') == 'moncash' ? 'selected' : '' }}>📱 MonCash</option>
                                <option value="bank_transfer" {{ old('method') == 'bank_transfer' ? 'selected' : '' }}>🏦 Virement Bancaire</option>
                            </select>
                            @error('method')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Note -->
                        <div class="mb-8">
                            <label for="withdrawal_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Note (optionnel)
                            </label>
                            <textarea
                                id="withdrawal_note"
                                name="note"
                                rows="3"
                                maxlength="500"
                                placeholder="Raison du retrait, remarques..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >{{ old('note') }}</textarea>
                            @error('note')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-end gap-3 pt-6 border-t dark:border-gray-700">
                            <a
                                href="{{ route('accounts.show', $account) }}"
                                class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition-all"
                            >
                                Annuler
                            </a>
                            <button
                                type="submit"
                                class="px-8 py-2.5 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white text-sm font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center gap-2"
                            >
                                <span>✅</span>
                                Confirmer le retrait
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const soldeActuel = {{ $account->getAvailableForWithdrawal() }};

        function calculateWithdrawal() {
            const amountInput = document.getElementById('withdrawal_amount');
            if (!amountInput) return;

            const amount = parseFloat(amountInput.value) || 0;
            const withdrawalInfo = document.getElementById('withdrawal-info');

            if (withdrawalInfo && amount > 0) {
                const percentage = ((amount / soldeActuel) * 100).toFixed(1);
                const isTotal = amount >= soldeActuel;

                if (isTotal) {
                    withdrawalInfo.textContent = `🔴 Retrait TOTAL (100%) - Le compte sera clôturé`;
                    withdrawalInfo.classList.remove('text-blue-600', 'dark:text-blue-400', 'text-orange-600', 'dark:text-orange-400');
                    withdrawalInfo.classList.add('text-red-600', 'dark:text-red-400');
                } else {
                    withdrawalInfo.textContent = `🟢 Retrait partiel (${percentage}%) - Le solde restant sera ${(soldeActuel - amount).toFixed(2)} HTG`;
                    withdrawalInfo.classList.remove('text-red-600', 'dark:text-red-400', 'text-blue-600', 'dark:text-blue-400');
                    withdrawalInfo.classList.add('text-green-600', 'dark:text-green-400');
                }
            } else if (withdrawalInfo) {
                withdrawalInfo.textContent = '';
            }
        }
    </script>
</x-app-layout>
