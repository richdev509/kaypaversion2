<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Tableau de Bord Analytique') }}
            </h2>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 dark:text-gray-400">Mise à jour en temps réel</span>
                <div id="refresh-indicator" class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtre par branche -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <form id="filterForm" method="GET" action="{{ route('dashboard.analytics') }}" class="flex items-end gap-3">
                        <div class="flex-1 max-w-xs">
                            <label for="branch_id" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Filtrer par Succursale
                            </label>
                            <select name="branch_id" id="branch_id"
                                    class="block w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                    onchange="this.form.submit()">
                                <option value="">Toutes les succursales</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if($branchId)
                        <a href="{{ route('dashboard.analytics') }}"
                           class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                            Réinitialiser
                        </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Cartes de statistiques principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Montant Total Disponible -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Montant Total</h3>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold" id="totalBalance">{{ number_format($stats['totalBalance'], 2) }}</div>
                        <div class="text-xs opacity-75 mt-1">HTG disponible</div>
                    </div>
                </div>

                <!-- Nombre de Clients -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Clients</h3>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold" id="totalClients">{{ number_format($stats['totalClients']) }}</div>
                        <div class="text-xs opacity-75 mt-1">Clients actifs</div>
                    </div>
                </div>

                <!-- Nombre de Comptes -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Comptes</h3>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div class="text-3xl font-bold" id="totalAccounts">{{ number_format($stats['totalAccounts']) }}</div>
                        <div class="text-xs opacity-75 mt-1">{{ $stats['activeAccounts'] }} actifs</div>
                    </div>
                </div>

                <!-- Transactions du Jour -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium opacity-90">Aujourd'hui</h3>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <div class="text-2xl font-bold" id="todayTransactions">{{ $stats['todayPayments'] + $stats['todayWithdrawals'] }}</div>
                            <div class="text-xs opacity-75">transactions</div>
                        </div>
                        <div class="text-xs opacity-75 mt-1">{{ number_format($stats['todayPaymentsAmount'] + $stats['todayWithdrawalsAmount'], 2) }} HTG</div>
                    </div>
                </div>
            </div>

            <!-- Section Dépôts et Retraits -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Statistiques Dépôts -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                💰 Dépôts (Paiements)
                            </h3>
                            <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                ↑ Entrées
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Dépôts</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100" id="totalPayments">
                                        {{ number_format($stats['totalPayments']) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Montant Total</div>
                                    <div class="text-xl font-bold text-green-600" id="totalPaymentsAmount">
                                        {{ number_format($stats['totalPaymentsAmount'], 2) }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border-2 border-green-200 dark:border-green-800">
                                <div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Aujourd'hui</div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="todayPayments">
                                        {{ number_format($stats['todayPayments']) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Montant</div>
                                    <div class="text-lg font-bold text-green-600" id="todayPaymentsAmount">
                                        {{ number_format($stats['todayPaymentsAmount'], 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques Retraits -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                💸 Retraits
                            </h3>
                            <span class="px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                ↓ Sorties
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Retraits</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100" id="totalWithdrawals">
                                        {{ number_format($stats['totalWithdrawals']) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Montant Total</div>
                                    <div class="text-xl font-bold text-red-600" id="totalWithdrawalsAmount">
                                        {{ number_format($stats['totalWithdrawalsAmount'], 2) }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border-2 border-red-200 dark:border-red-800">
                                <div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Aujourd'hui</div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100" id="todayWithdrawals">
                                        {{ number_format($stats['todayWithdrawals']) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Montant</div>
                                    <div class="text-lg font-bold text-red-600" id="todayWithdrawalsAmount">
                                        {{ number_format($stats['todayWithdrawalsAmount'], 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Graphique des transactions 7 derniers jours -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="p-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            📊 Transactions - 7 Derniers Jours
                        </h3>
                        <div style="height: 140px; position: relative;">
                            <canvas id="transactionsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Graphique Comptes Actifs vs Inactifs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            📈 État des Comptes
                        </h3>
                        <div style="height: 140px; position: relative;">
                            <canvas id="accountsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribution par succursale et Top 5 Clients -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Distribution par succursale -->
                @if($chartsData['branchesData']->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            🏢 Distribution par Succursale
                        </h3>
                        <div style="height: 160px; position: relative;">
                            <canvas id="branchesChart"></canvas>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Top 5 Clients -->
                @if($chartsData['topClients']->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            🌟 Top 5 Clients (Par Solde)
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Rang
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Client
                                        </th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Solde
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($chartsData['topClients'] as $index => $client)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs
                                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : ($index === 1 ? 'bg-gray-200 text-gray-700' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700')) }}
                                                font-bold">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $client['name'] }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-semibold text-green-600">
                                            {{ number_format($client['balance'], 0) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Configuration des graphiques
        const chartColors = {
            primary: 'rgb(59, 130, 246)',
            success: 'rgb(34, 197, 94)',
            danger: 'rgb(239, 68, 68)',
            warning: 'rgb(251, 146, 60)',
            purple: 'rgb(168, 85, 247)',
        };

        // Graphique Transactions 7 derniers jours
        const transactionsCtx = document.getElementById('transactionsChart').getContext('2d');
        const transactionsChart = new Chart(transactionsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartsData['last7Days']) !!},
                datasets: [
                    {
                        label: 'Dépôts',
                        data: {!! json_encode($chartsData['paymentsLast7Days']) !!},
                        backgroundColor: chartColors.success,
                        borderColor: chartColors.success,
                        borderWidth: 1
                    },
                    {
                        label: 'Retraits',
                        data: {!! json_encode($chartsData['withdrawalsLast7Days']) !!},
                        backgroundColor: chartColors.danger,
                        borderColor: chartColors.danger,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' HTG';
                            }
                        }
                    }
                }
            }
        });

        // Graphique Comptes Actifs vs Inactifs
        const accountsCtx = document.getElementById('accountsChart').getContext('2d');
        const accountsChart = new Chart(accountsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Actifs', 'Inactifs'],
                datasets: [{
                    data: [{{ $stats['activeAccounts'] }}, {{ $stats['inactiveAccounts'] }}],
                    backgroundColor: [chartColors.success, chartColors.danger],
                    borderColor: ['#fff', '#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Graphique Distribution par succursale
        @if($chartsData['branchesData']->count() > 0)
        const branchesCtx = document.getElementById('branchesChart').getContext('2d');
        const branchesChart = new Chart(branchesCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartsData['branchesData']->pluck('name')) !!},
                datasets: [
                    {
                        label: 'Clients',
                        data: {!! json_encode($chartsData['branchesData']->pluck('clients')) !!},
                        backgroundColor: chartColors.primary,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Comptes',
                        data: {!! json_encode($chartsData['branchesData']->pluck('accounts')) !!},
                        backgroundColor: chartColors.purple,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Solde Total (HTG)',
                        data: {!! json_encode($chartsData['branchesData']->pluck('balance')) !!},
                        type: 'line',
                        backgroundColor: chartColors.success,
                        borderColor: chartColors.success,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Nombre'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Solde (HTG)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
        @endif

        // Mise à jour automatique toutes les 30 secondes
        setInterval(function() {
            const branchId = document.getElementById('branch_id').value;
            fetch(`{{ route('dashboard.realtime-stats') }}?branch_id=${branchId}`)
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour les cartes
                    document.getElementById('totalBalance').textContent = parseFloat(data.totalBalance).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    document.getElementById('totalClients').textContent = parseInt(data.totalClients).toLocaleString('fr-FR');
                    document.getElementById('totalAccounts').textContent = parseInt(data.totalAccounts).toLocaleString('fr-FR');
                    document.getElementById('todayTransactions').textContent = (parseInt(data.todayPayments) + parseInt(data.todayWithdrawals)).toLocaleString('fr-FR');

                    document.getElementById('totalPayments').textContent = parseInt(data.totalPayments).toLocaleString('fr-FR');
                    document.getElementById('totalPaymentsAmount').textContent = parseFloat(data.totalPaymentsAmount).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    document.getElementById('todayPayments').textContent = parseInt(data.todayPayments).toLocaleString('fr-FR');
                    document.getElementById('todayPaymentsAmount').textContent = parseFloat(data.todayPaymentsAmount).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    document.getElementById('totalWithdrawals').textContent = parseInt(data.totalWithdrawals).toLocaleString('fr-FR');
                    document.getElementById('totalWithdrawalsAmount').textContent = parseFloat(data.totalWithdrawalsAmount).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    document.getElementById('todayWithdrawals').textContent = parseInt(data.todayWithdrawals).toLocaleString('fr-FR');
                    document.getElementById('todayWithdrawalsAmount').textContent = parseFloat(data.todayWithdrawalsAmount).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    // Animation de l'indicateur
                    const indicator = document.getElementById('refresh-indicator');
                    indicator.classList.remove('animate-pulse');
                    setTimeout(() => indicator.classList.add('animate-pulse'), 100);
                })
                .catch(error => console.error('Erreur de mise à jour:', error));
        }, 30000); // 30 secondes
    </script>
    @endpush
</x-app-layout>
