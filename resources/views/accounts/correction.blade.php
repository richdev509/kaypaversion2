<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('🔧 Corrections de Transactions') }} - Compte {{ $account->account_id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Info Compte -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informations du Compte</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Client:</span>
                            <span class="font-medium">{{ $account->client->nom }} {{ $account->client->prenom }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Solde actuel:</span>
                            <span class="font-bold text-lg">{{ number_format($account->amount_after, 2) }} HTG</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Annuler une Transaction Récente -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-red-600 dark:text-red-400">
                            ❌ Annuler une Transaction
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Annule une transaction existante et inverse automatiquement son impact sur les soldes. (2 dernières transactions)
                        </p>

                        @if($recentTransactions->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentTransactions as $trans)
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $trans->type === 'PAIEMENT' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $trans->type }}
                                        </span>
                                        <span class="ml-2 font-bold">{{ number_format($trans->amount, 2) }} HTG</span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $trans->created_at->diffForHumans() }}</span>
                                </div>

                                <form method="POST" action="{{ route('transactions.cancel', $trans) }}"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette transaction?');">
                                    @csrf
                                    <textarea name="cancellation_reason" rows="2" required
                                              placeholder="Raison de l'annulation (min. 10 caractères)"
                                              class="block w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 mb-2"></textarea>
                                    <button type="submit" class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm">
                                        Annuler cette transaction
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-center text-gray-500 py-4">
                            Aucune transaction récente à annuler
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Créer un Ajustement -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-blue-600 dark:text-blue-400">
                            ⚙️ Créer un Ajustement
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Crée une nouvelle transaction de correction pour augmenter ou diminuer le solde.
                        </p>

                        <form method="POST" action="{{ route('accounts.adjustments.create', $account) }}">
                            @csrf

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Type d'ajustement</label>
                                <select name="adjustment_type" required
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="increase">➕ Augmenter le solde</option>
                                    <option value="decrease">➖ Diminuer le solde</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Montant (HTG)</label>
                                <input type="number" name="amount" step="0.01" min="0.01" required
                                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Raison (détaillée)</label>
                                <textarea name="reason" rows="3" required
                                          placeholder="Expliquez en détail la raison de cet ajustement (min. 10 caractères)"
                                          class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                            </div>

                            <button type="submit" class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium">
                                Créer l'Ajustement
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Retour -->
            <div class="mt-6">
                <a href="{{ route('accounts.show', $account) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                    ← Retour au compte
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
