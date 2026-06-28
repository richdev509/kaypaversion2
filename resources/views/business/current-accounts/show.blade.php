<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('business.entities.show', $account->business) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight font-mono">
                        {{ $account->account_number }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $account->business->name }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('business.kcb.deposit.form', $account) }}"
                   class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    Dépôt
                </a>
                <a href="{{ route('business.kcb.withdraw.form', $account) }}"
                   class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    Retrait
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
            @endif

            <!-- Solde -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Solde</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ number_format($account->balance, 2) }} <span class="text-sm font-medium">HTG</span>
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Statut</p>
                    <p class="text-lg font-semibold {{ $account->isActive() ? 'text-green-600 dark:text-green-400' : 'text-gray-500' }} mt-2">
                        {{ $account->getStatusLabel() }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dernier mouvement</p>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">
                        {{ $account->last_flux_at?->format('d/m/Y H:i') ?? '—' }}
                    </p>
                </div>
            </div>

            <!-- Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Historique des transactions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">ID Transaction</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Méthode</th>
                                <th class="px-4 py-3 text-right">Montant</th>
                                <th class="px-4 py-3 text-right">Solde après</th>
                                <th class="px-4 py-3 text-left">Par</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($transactions as $tx)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                    {{ $tx->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                    {{ $tx->transaction_id }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="{{ $tx->isCredit() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                        {{ $tx->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $tx->method ?? '—' }}</td>
                                <td class="px-4 py-3 text-right {{ $tx->isCredit() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                    {{ $tx->isDebit() ? '-' : '+' }}{{ number_format($tx->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                    {{ number_format($tx->balance_after, 2) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $tx->creator?->name ?? '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucune transaction.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
