<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Toutes les transactions
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Compte : <span class="font-medium text-gray-700 dark:text-gray-300">{{ $account->account_id }}</span>
                    — {{ $account->client->full_name }}
                </p>
            </div>
            <a href="{{ route('accounts.show', $account) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour au compte
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Statistiques -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total déposé -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-2xl">
                        💰
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total déposé</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                            +{{ number_format($stats->total_deposits, 2) }} HTG
                        </p>
                    </div>
                </div>

                <!-- Total retiré -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center text-2xl">
                        💸
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total retiré</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">
                            -{{ number_format($stats->total_withdrawals, 2) }} HTG
                        </p>
                    </div>
                </div>

                <!-- Solde actuel -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-2xl">
                        📊
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Solde actuel</p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($account->solde_virtuel ?? $account->balance ?? 0, 2) }} HTG
                        </p>
                    </div>
                </div>

                <!-- Nombre de transactions -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-2xl">
                        🧾
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Transactions</p>
                        <p class="text-lg font-bold text-gray-700 dark:text-gray-200">
                            {{ number_format($stats->total_count) }}
                            @if($stats->cancelled_count > 0)
                                <span class="text-xs font-normal text-red-400">({{ $stats->cancelled_count }} annulée{{ $stats->cancelled_count > 1 ? 's' : '' }})</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tableau -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                        Historique complet des transactions
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $transactions->total() }} transaction(s) au total
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solde après</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Méthode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Opérateur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Note</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $transaction->status === 'CANCELLED' ? 'opacity-60' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($transaction->type === 'PAIEMENT' || $transaction->type === 'Paiement initial')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Dépôt
                                        </span>
                                    @elseif($transaction->type === 'RETRAIT')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Retrait
                                        </span>
                                    @elseif($transaction->type === 'AJUSTEMENT')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            Ajustement
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $transaction->type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $transaction->amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($transaction->amount_after, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ strtoupper($transaction->method ?? '—') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    @if($transaction->creator)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs flex items-center justify-center font-bold">
                                                {{ strtoupper(substr($transaction->creator->name, 0, 1)) }}
                                            </span>
                                            {{ $transaction->creator->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Système</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $transaction->note ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($transaction->status === 'CANCELLED')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                                            Annulée
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                            Valide
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    Aucune transaction enregistrée pour ce compte.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
