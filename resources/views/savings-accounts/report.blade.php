<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('savings-accounts.dashboard') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 print:hidden">← Dashboard</a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Rapport Financier — Comptes Épargne</h2>
            </div>
            @if($generated)
            <button onclick="window.print()" class="print:hidden px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer / PDF
            </button>
            @endif
        </div>
    </x-slot>

    <style>
        @media print {
            .print\:hidden { display: none !important; }
            body, .print-page { background: white !important; color: black !important; }
            .shadow-sm { box-shadow: none !important; }
        }
    </style>

    <div class="py-8 print-page">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Formulaire --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 print:hidden">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Paramètres du rapport</h3>
                <form method="GET" action="{{ route('savings-accounts.report') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de début <span class="text-red-500">*</span></label>
                        <input type="date" name="date_debut" value="{{ request('date_debut', $dateDebut->format('Y-m-d')) }}" required
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de fin <span class="text-red-500">*</span></label>
                        <input type="date" name="date_fin" value="{{ request('date_fin', $dateFin->format('Y-m-d')) }}" required
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium">Générer</button>
                        <a href="{{ route('savings-accounts.report', ['date_debut' => now()->startOfMonth()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}"
                           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">Ce mois</a>
                        <a href="{{ route('savings-accounts.report', ['date_debut' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'date_fin' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}"
                           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">Mois précédent</a>
                        <a href="{{ route('savings-accounts.report', ['date_debut' => now()->startOfYear()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}"
                           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">Cette année</a>
                    </div>
                </form>
            </div>

            @if(!$generated)
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">Sélectionnez une plage de dates et cliquez sur <strong>Générer</strong>.</p>
            </div>

            @else

            {{-- En-tête rapport --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">KAYPA — Rapport Financier</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Comptes Épargne</p>
                    </div>
                    <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
                        <p>Par : {{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg inline-flex items-center gap-3">
                    <span class="font-semibold text-emerald-800 dark:text-emerald-200">
                        Période : {{ $dateDebut->format('d/m/Y') }} → {{ $dateFin->format('d/m/Y') }}
                    </span>
                    <span class="text-emerald-600 dark:text-emerald-400 text-sm">
                        ({{ (int) $dateDebut->copy()->startOfDay()->diffInDays($dateFin->copy()->startOfDay()) + 1 }} jour(s))
                    </span>
                </div>
            </div>

            {{-- KPI --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 border-green-500">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total dépôts</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($flux['depots'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $flux['depots_nb'] }} op. · GDS</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 border-red-500">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total retraits</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($flux['retraits'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $flux['retraits_nb'] }} op. · GDS</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 {{ $flux['net'] >= 0 ? 'border-blue-500' : 'border-orange-500' }}">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Flux net</p>
                    <p class="text-2xl font-bold {{ $flux['net'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }} mt-1">
                        {{ $flux['net'] >= 0 ? '+' : '' }}{{ number_format($flux['net'], 2) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">GDS</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 border-emerald-500">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Intérêts versés</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($flux['interets'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $flux['interets_nb'] }} op. · GDS</p>
                </div>
            </div>

            {{-- Récapitulatif --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Récapitulatif des opérations</h3>
                </div>
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nb opérations</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant total (GDS)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr class="bg-green-50 dark:bg-green-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-green-800 dark:text-green-200">Dépôts</td>
                            <td class="px-6 py-3 text-sm text-right">{{ number_format($flux['depots_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-green-700 dark:text-green-300">+ {{ number_format($flux['depots'], 2) }}</td>
                        </tr>
                        <tr class="bg-red-50 dark:bg-red-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-red-800 dark:text-red-200">Retraits</td>
                            <td class="px-6 py-3 text-sm text-right">{{ number_format($flux['retraits_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-red-700 dark:text-red-300">- {{ number_format($flux['retraits'], 2) }}</td>
                        </tr>
                        <tr class="bg-purple-50 dark:bg-purple-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-purple-800 dark:text-purple-200">Frais d'ouverture</td>
                            <td class="px-6 py-3 text-sm text-right">{{ number_format($flux['frais_ouverture_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-purple-700 dark:text-purple-300">{{ number_format($flux['frais_ouverture'], 2) }}</td>
                        </tr>
                        <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-emerald-800 dark:text-emerald-200">Intérêts versés (charge)</td>
                            <td class="px-6 py-3 text-sm text-right">{{ number_format($flux['interets_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($flux['interets'], 2) }}</td>
                        </tr>
                        <tr class="bg-gray-100 dark:bg-gray-700 font-semibold">
                            <td class="px-6 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">TOTAL opérations</td>
                            <td class="px-6 py-3 text-sm text-right">{{ number_format($flux['total_ops']) }}</td>
                            <td class="px-6 py-3 text-sm text-right">—</td>
                        </tr>
                        <tr class="bg-blue-50 dark:bg-blue-900/30">
                            <td class="px-6 py-3 text-sm font-bold text-blue-900 dark:text-blue-100">FLUX NET (dépôts − retraits)</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-500">—</td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-blue-700 dark:text-blue-300 text-base">
                                {{ $flux['net'] >= 0 ? '+' : '' }}{{ number_format($flux['net'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Détail journalier --}}
            @if($joursDetail->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Détail journalier</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Dépôts</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wider">Retraits</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Intérêts</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Flux net</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            @php $totD = 0; $totR = 0; $totI = 0; @endphp
                            @foreach($joursDetail as $jour => $lignes)
                            @php
                                $d = (float) $lignes->firstWhere('type', 'DEPOT')?->total   ?? 0;
                                $r = (float) $lignes->firstWhere('type', 'RETRAIT')?->total ?? 0;
                                $i = (float) $lignes->firstWhere('type', 'INTERET')?->total ?? 0;
                                $net = $d - $r;
                                $totD += $d; $totR += $r; $totI += $i;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-5 py-2.5 text-sm font-medium text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($jour)->format('d/m/Y') }}</td>
                                <td class="px-5 py-2.5 text-sm text-right {{ $d > 0 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-400' }}">{{ $d > 0 ? number_format($d, 2) : '—' }}</td>
                                <td class="px-5 py-2.5 text-sm text-right {{ $r > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-400' }}">{{ $r > 0 ? number_format($r, 2) : '—' }}</td>
                                <td class="px-5 py-2.5 text-sm text-right {{ $i > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">{{ $i > 0 ? number_format($i, 2) : '—' }}</td>
                                <td class="px-5 py-2.5 text-sm text-right font-semibold {{ $net >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                                </td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray-100 dark:bg-gray-700 border-t-2 border-gray-300 dark:border-gray-500">
                                <td class="px-5 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">TOTAL</td>
                                <td class="px-5 py-3 text-sm text-right text-green-700 dark:text-green-300 font-bold">{{ number_format($totD, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-right text-red-700 dark:text-red-300 font-bold">{{ number_format($totR, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-right text-emerald-700 dark:text-emerald-300 font-bold">{{ number_format($totI, 2) }}</td>
                                @php $netTotal = $totD - $totR; @endphp
                                <td class="px-5 py-3 text-sm text-right font-bold {{ $netTotal >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }}">
                                    {{ $netTotal >= 0 ? '+' : '' }}{{ number_format($netTotal, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Par succursale --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Répartition par succursale</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Succursale</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Comptes actifs</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solde actuel</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Dépôts (période)</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wider">Retraits (période)</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Intérêts</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($parBranche as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row->branche }}</td>
                            <td class="px-5 py-3 text-sm text-right">{{ number_format($row->nb_actif) }}</td>
                            <td class="px-5 py-3 text-sm text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($row->solde_total, 2) }} GDS</td>
                            <td class="px-5 py-3 text-sm text-right text-green-600 dark:text-green-400">
                                +{{ number_format($row->depot_periode, 2) }} <span class="text-xs text-gray-400">({{ $row->depot_nb }})</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-right text-red-600 dark:text-red-400">
                                -{{ number_format($row->retrait_periode, 2) }} <span class="text-xs text-gray-400">({{ $row->retrait_nb }})</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-right text-emerald-600 dark:text-emerald-400">{{ number_format($row->interet_periode, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Aucune donnée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Infos complémentaires --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Informations complémentaires</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Comptes ouverts sur la période</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($ouvertures) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Solde total (comptes actifs)</p>
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($soldeFin, 2) }} GDS</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Total opérations enregistrées</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($flux['total_ops']) }}</p>
                    </div>
                </div>
            </div>

            <div class="text-center text-xs text-gray-400 dark:text-gray-500 py-2">
                Rapport généré automatiquement par KAYPA — {{ now()->format('d/m/Y H:i') }}
                · Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
            </div>

            @endif
        </div>
    </div>
</x-app-layout>
