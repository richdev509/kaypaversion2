<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📄 Détails de la Transaction #{{ $transaction->id }}
            </h2>
            <a href="{{ route('online-payments.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- STATUT DE LA TRANSACTION -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Transaction {{ $transaction->type_libelle }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Créée le {{ $transaction->created_at->format('d/m/Y à H:i:s') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-2 text-lg font-bold rounded-lg
                            @if($transaction->statut == 'reussie') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($transaction->statut == 'en_cours') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($transaction->statut == 'echouee') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                            @endif">
                            {{ $transaction->statut_libelle }}
                        </span>
                    </div>
                </div>

                <!-- MONTANT -->
                <div class="text-center py-8 bg-gray-50 dark:bg-gray-900 rounded-lg mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Montant de la transaction</p>
                    <p class="text-5xl font-bold
                        @if($transaction->type == 'retrait') text-red-600 dark:text-red-400
                        @else text-green-600 dark:text-green-400
                        @endif">
                        @if($transaction->type == 'retrait')-@endif{{ number_format($transaction->montant, 2) }} HTG
                    </p>
                </div>

                <!-- INFORMATIONS PRINCIPALES -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Colonne Gauche -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">ID Transaction</label>
                            <p class="text-lg font-mono font-bold text-gray-900 dark:text-gray-100">#{{ $transaction->id }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Numéro de Compte</label>
                            <p class="text-lg font-mono text-blue-600 dark:text-blue-400">{{ $transaction->account_id ?? 'N/A' }}</p>
                        </div>

                        @if($transaction->account && $transaction->account->client)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Client</label>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $transaction->account->client->first_name }} {{ $transaction->account->client->last_name }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                📞 {{ $transaction->account->client->phone }}
                            </p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                            <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full
                                @if($transaction->type == 'depot') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($transaction->type == 'retrait') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @endif">
                                {{ $transaction->type_libelle }}
                            </span>
                        </div>
                    </div>

                    <!-- Colonne Droite -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Ordre ID</label>
                            <p class="text-lg font-mono text-gray-900 dark:text-gray-100">{{ $transaction->ordre_id ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Gateway</label>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $transaction->gateway }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date de Création</label>
                            <p class="text-lg text-gray-900 dark:text-gray-100">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dernière Mise à Jour</label>
                            <p class="text-lg text-gray-900 dark:text-gray-100">{{ $transaction->updated_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BALANCE -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    💰 Évolution du Solde
                </h4>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Balance Avant</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($transaction->balance_avant, 2) }} HTG</p>
                    </div>

                    <div class="flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>

                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-2 border-blue-500">
                        <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Balance Après</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($transaction->balance_apres, 2) }} HTG</p>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Variation:
                        <span class="font-bold {{ ($transaction->balance_apres - $transaction->balance_avant) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ ($transaction->balance_apres - $transaction->balance_avant) >= 0 ? '+' : '' }}{{ number_format($transaction->balance_apres - $transaction->balance_avant, 2) }} HTG
                        </span>
                    </p>
                </div>
            </div>

            <!-- DESCRIPTION -->
            @if($transaction->description)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    📝 Description
                </h4>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $transaction->description }}</p>
            </div>
            @endif

            <!-- METADATA -->
            @if($transaction->metadata)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    🔍 Métadonnées Techniques
                </h4>
                <div class="bg-gray-50 dark:bg-gray-900 rounded p-4 overflow-x-auto">
                    <pre class="text-xs text-gray-700 dark:text-gray-300">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
            @endif

            <!-- INFORMATIONS COMPTE -->
            @if($transaction->account)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    👤 Informations du Compte
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Numéro de Compte</label>
                        <p class="text-lg font-mono text-blue-600 dark:text-blue-400">{{ $transaction->account->account_number }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Statut du Compte</label>
                        <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full
                            @if($transaction->account->status == 'actif') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                            @endif">
                            {{ ucfirst($transaction->account->status) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Solde Actuel du Compte</label>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($transaction->account->balance, 2) }} HTG</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date d'Ouverture</label>
                        <p class="text-lg text-gray-900 dark:text-gray-100">{{ $transaction->account->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>

                @if($transaction->account->client)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('clients.show', $transaction->account->client->id) }}"
                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                        → Voir le profil complet du client
                    </a>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
