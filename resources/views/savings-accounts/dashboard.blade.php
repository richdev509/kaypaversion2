<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('savings-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">← Retour</a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dashboard Comptable — Comptes Épargne
                </h2>
            </div>
            {{-- Filtre période --}}
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                @foreach(['today' => "Auj.", '7d' => '7 j', '30d' => '30 j', 'month' => 'Ce mois'] as $key => $label)
                <a href="{{ route('savings-accounts.dashboard', ['periode' => $key]) }}"
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

            {{-- ── KPI ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-emerald-600 dark:bg-emerald-700 shadow-sm sm:rounded-lg p-5 text-white col-span-2 md:col-span-1">
                    <p class="text-sm font-medium text-emerald-100">Solde total (actifs)</p>
                    <p class="text-3xl font-bold mt-1">{{ number_format($kpi['solde_total'], 2) }}</p>
                    <p class="text-xs text-emerald-200 mt-1">GDS · Taux {{ $tauxMensuel }}%/mois</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Comptes actifs</p>
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($kpi['nb_actif']) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Suspendus : {{ $kpi['nb_suspendu'] }} &nbsp;|&nbsp; Clôturés : {{ $kpi['nb_cloture'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dépôts aujourd'hui</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($kpi['depot_today'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $kpi['depot_today_nb'] }} opération(s)</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Retraits aujourd'hui</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($kpi['retrait_today'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $kpi['retrait_today_nb'] }} opération(s)</p>
                </div>
            </div>

            {{-- ── Flux + Alertes ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

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
                            <p class="text-xs text-green-600 dark:text-green-400">GDS</p>
                        </div>
                        <div class="p-4 bg-red-50 dark:bg-red-900/30 rounded-lg">
                            <p class="text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wide">Retraits</p>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-1">{{ number_format($flux['retraits'], 2) }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400">GDS</p>
                        </div>
                        <div class="p-4 {{ $flux['net'] >= 0 ? 'bg-blue-50 dark:bg-blue-900/30' : 'bg-orange-50 dark:bg-orange-900/30' }} rounded-lg">
                            <p class="text-xs font-medium {{ $flux['net'] >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }} uppercase tracking-wide">Flux net</p>
                            <p class="text-2xl font-bold {{ $flux['net'] >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }} mt-1">
                                {{ $flux['net'] >= 0 ? '+' : '' }}{{ number_format($flux['net'], 2) }}
                            </p>
                            <p class="text-xs {{ $flux['net'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}">GDS</p>
                        </div>
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                            <p class="text-xs font-medium text-purple-700 dark:text-purple-300 uppercase tracking-wide">Frais ouverture</p>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-300 mt-1">{{ number_format($flux['frais_ouverture'], 2) }}</p>
                            <p class="text-xs text-purple-600 dark:text-purple-400">GDS</p>
                        </div>
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg border-2 border-emerald-200 dark:border-emerald-700 col-span-2 md:col-span-1">
                            <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">Intérêts versés</p>
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">{{ number_format($flux['interets'], 2) }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">GDS (charge)</p>
                        </div>
                    </div>
                </div>

                {{-- Alertes --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Alertes comptables</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 {{ $alertes['sous_minimum'] > 0 ? 'bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">{{ $alertes['sous_minimum'] > 0 ? '🔴' : '✅' }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Sous le minimum</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Solde &lt; solde plancher</p>
                                </div>
                            </div>
                            <span class="text-xl font-bold {{ $alertes['sous_minimum'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">{{ $alertes['sous_minimum'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 {{ $alertes['interet_non_verse'] > 0 ? 'bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">{{ $alertes['interet_non_verse'] > 0 ? '🟡' : '✅' }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Intérêts non versés</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Actifs sans intérêt ce mois</p>
                                </div>
                            </div>
                            <span class="text-xl font-bold {{ $alertes['interet_non_verse'] > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-400' }}">{{ $alertes['interet_non_verse'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 {{ $alertes['suspendus_30j'] > 0 ? 'bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-700' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg">{{ $alertes['suspendus_30j'] > 0 ? '🟠' : '✅' }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Suspendus 30j+</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sans réactivation</p>
                                </div>
                            </div>
                            <span class="text-xl font-bold {{ $alertes['suspendus_30j'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400' }}">{{ $alertes['suspendus_30j'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Graphique 30 jours ── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Évolution dépôts vs retraits — 30 derniers jours</h3>
                <div class="relative h-64">
                    <canvas id="fluxChart"></canvas>
                </div>
            </div>

            {{-- ── Par succursale ── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Répartition par succursale</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Soldes et mouvements du mois en cours</p>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row->branche }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($row->nb_actif) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($row->solde_total, 2) }} GDS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">+{{ number_format($row->depot_mois, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 dark:text-red-400">-{{ number_format($row->retrait_mois, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $net >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Aucun compte épargne enregistré.</td></tr>
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
        const isDark    = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const lblColor  = isDark ? '#9ca3af' : '#6b7280';

        new Chart(document.getElementById('fluxChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Dépôts',
                        data: {!! json_encode($chartDepots) !!},
                        borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.12)',
                        borderWidth: 2, pointRadius: 3, tension: 0.3, fill: true,
                    },
                    {
                        label: 'Retraits',
                        data: {!! json_encode($chartRetraits) !!},
                        borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.10)',
                        borderWidth: 2, pointRadius: 3, tension: 0.3, fill: true,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: lblColor, boxWidth: 12, padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ' : ' + new Intl.NumberFormat('fr-HT').format(ctx.parsed.y) + ' GDS'
                        }
                    }
                },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: lblColor, maxTicksLimit: 10 } },
                    y: { grid: { color: gridColor }, ticks: { color: lblColor,
                        callback: v => new Intl.NumberFormat('fr-HT', { notation: 'compact' }).format(v) } }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
