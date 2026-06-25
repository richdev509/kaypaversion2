<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('current-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    ← Retour
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dashboard Comptable — Comptes Courants
                </h2>
            </div>
            {{-- Filtre de période --}}
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                @foreach(['today' => "Auj.", '7d' => '7 j', '30d' => '30 j', 'month' => 'Ce mois'] as $key => $label)
                <a href="{{ route('current-accounts.dashboard', ['periode' => $key]) }}"
                   class="px-3 py-1.5 rounded-md text-sm font-medium transition
                          {{ $periode === $key
                             ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm'
                             : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── SECTION 1 : KPI en-tête ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Solde total --}}
                <div class="bg-blue-600 dark:bg-blue-700 shadow-sm sm:rounded-lg p-5 text-white col-span-2 md:col-span-1">
                    <p class="text-sm font-medium text-blue-100">Solde total (actifs)</p>
                    <p class="text-3xl font-bold mt-1">{{ number_format($kpi['solde_total'], 2) }}</p>
                    <p class="text-xs text-blue-200 mt-1">HTG</p>
                </div>
                {{-- Comptes actifs --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Comptes actifs</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($kpi['nb_actif']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Suspendus : {{ $kpi['nb_suspendu'] }} &nbsp;|&nbsp; Clôturés : {{ $kpi['nb_cloture'] }}</p>
                </div>
                {{-- Dépôts du jour --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dépôts aujourd'hui</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($kpi['depot_today'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $kpi['depot_today_nb'] }} opération(s)</p>
                </div>
                {{-- Retraits du jour --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Retraits aujourd'hui</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($kpi['retrait_today'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $kpi['retrait_today_nb'] }} opération(s)</p>
                </div>
            </div>

            {{-- ── SECTION 2 : Flux financiers + Alertes ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Flux sur la période --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        Flux financiers
                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                            — {{ ['today' => "Aujourd'hui", '7d' => '7 derniers jours', '30d' => '30 derniers jours', 'month' => 'Ce mois-ci'][$periode] }}
                        </span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-green-50 dark:bg-green-900/30 rounded-lg">
                            <p class="text-xs font-medium text-green-700 dark:text-green-300 uppercase tracking-wide">Dépôts</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-1">{{ number_format($flux['depots'], 2) }}</p>
                            <p class="text-xs text-green-600 dark:text-green-400">HTG</p>
                        </div>
                        <div class="p-4 bg-red-50 dark:bg-red-900/30 rounded-lg">
                            <p class="text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wide">Retraits</p>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">{{ number_format($flux['retraits'], 2) }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400">HTG</p>
                        </div>
                        <div class="p-4 {{ $flux['net'] >= 0 ? 'bg-blue-50 dark:bg-blue-900/30' : 'bg-orange-50 dark:bg-orange-900/30' }} rounded-lg">
                            <p class="text-xs font-medium {{ $flux['net'] >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }} uppercase tracking-wide">Flux net</p>
                            <p class="text-2xl font-bold {{ $flux['net'] >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }} mt-1">
                                {{ $flux['net'] >= 0 ? '+' : '' }}{{ number_format($flux['net'], 2) }}
                            </p>
                            <p class="text-xs {{ $flux['net'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">HTG</p>
                        </div>
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                            <p class="text-xs font-medium text-purple-700 dark:text-purple-300 uppercase tracking-wide">Frais ouverture</p>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-300 mt-1">{{ number_format($flux['frais_ouverture'], 2) }}</p>
                            <p class="text-xs text-purple-600 dark:text-purple-400">HTG</p>
                        </div>
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg">
                            <p class="text-xs font-medium text-yellow-700 dark:text-yellow-300 uppercase tracking-wide">Frais service</p>
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300 mt-1">{{ number_format($flux['frais_service'], 2) }}</p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400">HTG</p>
                        </div>
                        <div class="p-4 bg-teal-50 dark:bg-teal-900/30 rounded-lg border-2 border-teal-200 dark:border-teal-700">
                            <p class="text-xs font-medium text-teal-700 dark:text-teal-300 uppercase tracking-wide">Revenus frais</p>
                            <p class="text-2xl font-bold text-teal-700 dark:text-teal-300 mt-1">{{ number_format($flux['revenus_frais'], 2) }}</p>
                            <p class="text-xs text-teal-600 dark:text-teal-400">HTG</p>
                        </div>
                    </div>
                </div>

                {{-- Alertes comptables --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Alertes comptables</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 {{ $alertes['solde_negatif'] > 0 ? 'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">{{ $alertes['solde_negatif'] > 0 ? '🔴' : '✅' }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Soldes négatifs</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Comptes avec solde < 0</p>
                                </div>
                            </div>
                            <span class="text-xl font-bold {{ $alertes['solde_negatif'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                                {{ $alertes['solde_negatif'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 {{ $alertes['frais_non_preleves'] > 0 ? 'bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">{{ $alertes['frais_non_preleves'] > 0 ? '🟡' : '✅' }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Frais non prélevés</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Actifs sans frais ce mois</p>
                                </div>
                            </div>
                            <span class="text-xl font-bold {{ $alertes['frais_non_preleves'] > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-400' }}">
                                {{ $alertes['frais_non_preleves'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 {{ $alertes['suspendus_30j'] > 0 ? 'bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-700' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">{{ $alertes['suspendus_30j'] > 0 ? '🟠' : '✅' }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Suspendus 30j+</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sans réactivation depuis 30j</p>
                                </div>
                            </div>
                            <span class="text-xl font-bold {{ $alertes['suspendus_30j'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400' }}">
                                {{ $alertes['suspendus_30j'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 3 : Graphique 30 jours ── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Évolution dépôts vs retraits — 30 derniers jours</h3>
                <div class="relative h-64">
                    <canvas id="fluxChart"></canvas>
                </div>
            </div>

            {{-- ── SECTION 4 : Par succursale ── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Répartition par succursale</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Comptes actifs, soldes et mouvements du mois en cours</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Succursale</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Comptes actifs</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solde total</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dépôts (mois)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Retraits (mois)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Net (mois)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($parBranche as $row)
                            @php $net = $row->depot_mois - $row->retrait_mois; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $row->branche }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                    {{ number_format($row->nb_actif) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-blue-600 dark:text-blue-400">
                                    {{ number_format($row->solde_total, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">
                                    +{{ number_format($row->depot_mois, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 dark:text-red-400">
                                    -{{ number_format($row->retrait_mois, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $net >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }} HTG
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucun compte courant enregistré.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor  = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const labelColor = isDark ? '#9ca3af' : '#6b7280';

        const ctx = document.getElementById('fluxChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Dépôts',
                        data: {!! json_encode($chartDepots) !!},
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.12)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Retraits',
                        data: {!! json_encode($chartRetraits) !!},
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220,38,38,0.10)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.3,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: labelColor, boxWidth: 12, padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ' : ' + new Intl.NumberFormat('fr-HT').format(ctx.parsed.y) + ' HTG'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: labelColor, maxTicksLimit: 10 }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: labelColor,
                            callback: v => new Intl.NumberFormat('fr-HT', { notation: 'compact' }).format(v)
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
