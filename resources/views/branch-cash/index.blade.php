<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('💰 Gestion de Caisse - Succursale') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Messages de succès/erreur -->
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

            <!-- Filtre par succursale -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <form method="GET" action="{{ route('branch-cash.index') }}" class="flex items-end gap-3">
                        <div class="flex-1 max-w-xs">
                            <label for="branch_id" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sélectionner une Succursale
                            </label>
                            <select name="branch_id" id="branch_id"
                                    class="block w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    onchange="this.form.submit()">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            @if($branch)

            <!-- DEBUG TEMPORAIRE - À SUPPRIMER -->
            <div class="mb-4 p-4 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg">
                <strong>DEBUG:</strong><br>
                today_deposits = {{ $stats['today_deposits'] }}<br>
                today_withdrawals = {{ $stats['today_withdrawals'] }}<br>
                current_balance = {{ $stats['current_balance'] }}<br>
                today_net = {{ $stats['today_net'] }}
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Solde Caisse Actuel -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Solde Caisse</h3>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold">{{ number_format($stats['current_balance'], 2) }}</div>
                        <div class="text-xs opacity-75 mt-1">HTG disponible</div>
                    </div>
                </div>

                <!-- Dépôts du Jour -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Dépôts Jour</h3>
                            <span class="text-2xl">↑</span>
                        </div>
                        <div class="text-3xl font-bold">{{ number_format($stats['today_deposits'], 0) }}</div>
                        <div class="text-xs opacity-75 mt-1">HTG reçus</div>
                    </div>
                </div>

                <!-- Retraits du Jour -->
                <div class="bg-gradient-to-br from-red-500 to-red-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Retraits Jour</h3>
                            <span class="text-2xl">↓</span>
                        </div>
                        <div class="text-3xl font-bold">{{ number_format($stats['today_withdrawals'], 0) }}</div>
                        <div class="text-xs opacity-75 mt-1">HTG sortis</div>
                    </div>
                </div>

                <!-- Net du Jour -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Net Jour</h3>
                            <span class="text-2xl">{{ $stats['today_net'] >= 0 ? '✓' : '✗' }}</span>
                        </div>
                        <div class="text-3xl font-bold">{{ number_format($stats['today_net'], 0) }}</div>
                        <div class="text-xs opacity-75 mt-1">HTG {{ $stats['today_net'] >= 0 ? 'excédent' : 'déficit' }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            @if(Auth::user()->hasPermissionTo('fund-movements.view'))
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Gestion des Mouvements</h3>
                    <div class="flex gap-4">
                        <a href="{{ route('fund-movements.create') }}" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-center">
                            💰 Créer un Mouvement de Fonds
                        </a>
                        <a href="{{ route('fund-movements.index') }}" class="flex-1 px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium text-center">
                            📋 Historique des Mouvements
                        </a>
                    </div>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        💡 Utilisez les Mouvements de Fonds pour: réceptions (banque, injection initiale), décaissements (banque, externe) et transferts entre succursales.
                    </p>
                </div>
            </div>
            @endif

            <!-- Historique des mouvements -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        📋 Historique des Mouvements (30 derniers jours)
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Référence</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Montant</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Impact</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $transaction['date']->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $transaction['category'] === 'CLIENT' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $transaction['category'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $transaction['description'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $transaction['reference'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold
                                        {{ $transaction['impact'] === 'IN' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($transaction['amount'], 2) }} HTG
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                            {{ $transaction['impact'] === 'IN' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                            {{ $transaction['impact'] === 'IN' ? '↑' : '↓' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Aucun mouvement trouvé pour les 30 derniers jours.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Actualiser automatiquement les stats toutes les 30 secondes
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
    @endpush
</x-app-layout>
