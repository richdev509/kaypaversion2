<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💳 Paiements en Ligne - Dashboard
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('online-payments.export', request()->all()) }}"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                    📥 Exporter CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- SOLDE GLOBAL -->
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 text-white rounded-lg shadow-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <p class="text-sm opacity-90 mb-1">💰 Balance Actuelle</p>
                        <p class="text-3xl font-bold">{{ number_format($balance->balance, 2) }} HTG</p>
                        <p class="text-xs opacity-75 mt-1">
                            Dernière mise à jour: {{ $balance->derniere_transaction ? $balance->derniere_transaction->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm opacity-90 mb-1">📥 Total Dépôts</p>
                        <p class="text-2xl font-bold">{{ number_format($balance->total_depot, 2) }} HTG</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm opacity-90 mb-1">📤 Total Retraits</p>
                        <p class="text-2xl font-bold">{{ number_format($balance->total_retrait, 2) }} HTG</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm opacity-90 mb-1">🎫 Frais Ouverture</p>
                        <p class="text-2xl font-bold">{{ number_format($balance->total_ouverture, 2) }} HTG</p>
                        <p class="text-xs opacity-75 mt-1">{{ number_format($balance->nombre_transactions) }} transactions</p>
                    </div>
                </div>
            </div>

            <!-- STATISTIQUES DE LA PÉRIODE -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    📊 Statistiques de la Période
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <!-- Total Transactions -->
                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_transactions'] }}</p>
                    </div>

                    <!-- Montant Total -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Montant Total</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($stats['total_montant'], 0) }} HTG</p>
                    </div>

                    <!-- Réussies -->
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1">✅ Réussies</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $stats['reussie_count'] }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">Taux: {{ $stats['taux_reussite'] }}%</p>
                    </div>

                    <!-- Échouées -->
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                        <p class="text-xs text-red-600 dark:text-red-400 mb-1">❌ Échouées</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $stats['echouee_count'] }}</p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">En cours: {{ $stats['en_cours_count'] }}</p>
                    </div>
                </div>

                <!-- Statistiques par Type -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Dépôts -->
                    <div class="border-l-4 border-blue-500 bg-blue-50 dark:bg-blue-900/10 p-4 rounded">
                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">📥 Dépôts</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['depot_count'] }} transactions</p>
                        <p class="text-lg text-blue-800 dark:text-blue-200 mt-1">{{ number_format($stats['depot_montant'], 0) }} HTG</p>
                    </div>

                    <!-- Retraits -->
                    <div class="border-l-4 border-red-500 bg-red-50 dark:bg-red-900/10 p-4 rounded">
                        <p class="text-sm font-medium text-red-900 dark:text-red-100 mb-2">📤 Retraits</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $stats['retrait_count'] }} transactions</p>
                        <p class="text-lg text-red-800 dark:text-red-200 mt-1">{{ number_format($stats['retrait_montant'], 0) }} HTG</p>
                    </div>

                    <!-- Ouvertures -->
                    <div class="border-l-4 border-green-500 bg-green-50 dark:bg-green-900/10 p-4 rounded">
                        <p class="text-sm font-medium text-green-900 dark:text-green-100 mb-2">🎫 Ouvertures</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['ouverture_count'] }} transactions</p>
                        <p class="text-lg text-green-800 dark:text-green-200 mt-1">{{ number_format($stats['ouverture_montant'], 0) }} HTG</p>
                    </div>
                </div>
            </div>

            <!-- FILTRES -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form method="GET" action="{{ route('online-payments.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Date Début -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Début</label>
                            <input type="date" name="date_debut" value="{{ $dateDebut }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        </div>

                        <!-- Date Fin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Fin</label>
                            <input type="date" name="date_fin" value="{{ $dateFin }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Tous</option>
                                <option value="depot" {{ $type == 'depot' ? 'selected' : '' }}>Dépôt</option>
                                <option value="retrait" {{ $type == 'retrait' ? 'selected' : '' }}>Retrait</option>
                                <option value="ouverture" {{ $type == 'ouverture' ? 'selected' : '' }}>Ouverture</option>
                            </select>
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                            <select name="statut" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Tous</option>
                                <option value="reussie" {{ $statut == 'reussie' ? 'selected' : '' }}>Réussie</option>
                                <option value="en_cours" {{ $statut == 'en_cours' ? 'selected' : '' }}>En cours</option>
                                <option value="echouee" {{ $statut == 'echouee' ? 'selected' : '' }}>Échouée</option>
                                <option value="annulee" {{ $statut == 'annulee' ? 'selected' : '' }}>Annulée</option>
                            </select>
                        </div>

                        <!-- Recherche -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Compte, Ordre ID..."
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            🔍 Filtrer
                        </button>
                        <a href="{{ route('online-payments.index') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition">
                            🔄 Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- LISTE DES TRANSACTIONS -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        📋 Transactions ({{ $transactions->total() }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Compte</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gateway</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono text-blue-600 dark:text-blue-400">{{ $transaction->account_id }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        @if($transaction->account && $transaction->account->client)
                                            {{ $transaction->account->client->first_name }} {{ $transaction->account->client->last_name }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($transaction->type == 'depot') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($transaction->type == 'retrait') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @endif">
                                            {{ $transaction->type_libelle }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold
                                        @if($transaction->type == 'retrait') text-red-600 dark:text-red-400
                                        @else text-green-600 dark:text-green-400
                                        @endif">
                                        {{ number_format($transaction->montant, 2) }} HTG
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $transaction->gateway }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($transaction->statut == 'reussie') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($transaction->statut == 'en_cours') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($transaction->statut == 'echouee') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                            @endif">
                                            {{ $transaction->statut_libelle }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('online-payments.show', $transaction->id) }}"
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                            👁️ Détails
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Aucune transaction trouvée pour cette période
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
