<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('current-accounts.dashboard') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 print:hidden">
                    ← Dashboard
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Rapport Financier — Comptes Courants
                </h2>
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

    {{-- ── Styles d'impression ── --}}
    <style>
        @media print {
            .print\:hidden { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-page { background: white !important; color: black !important; }
            .dark\:bg-gray-800, .dark\:bg-gray-700 { background: white !important; }
            .shadow-sm { box-shadow: none !important; }
            table { page-break-inside: avoid; }
            h3 { page-break-after: avoid; }
            .page-break { page-break-before: always; }
        }
    </style>

    <div class="py-8 print-page">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Formulaire sélecteur de dates ── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 print:hidden">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Paramètres du rapport</h3>
                <form method="GET" action="{{ route('current-accounts.report') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Date de début <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_debut"
                            value="{{ request('date_debut', $dateDebut->format('Y-m-d')) }}"
                            required
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Date de fin <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_fin"
                            value="{{ request('date_fin', $dateFin->format('Y-m-d')) }}"
                            required
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                            Générer le rapport
                        </button>
                        {{-- Raccourcis rapides --}}
                        <a href="{{ route('current-accounts.report', ['date_debut' => now()->startOfMonth()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}"
                           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">
                            Ce mois
                        </a>
                        <a href="{{ route('current-accounts.report', ['date_debut' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'date_fin' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}"
                           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">
                            Mois précédent
                        </a>
                        <a href="{{ route('current-accounts.report', ['date_debut' => now()->startOfYear()->format('Y-m-d'), 'date_fin' => now()->format('Y-m-d')]) }}"
                           class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium">
                            Cette année
                        </a>
                    </div>
                </form>
                @if($errors->any())
                <p class="mt-2 text-sm text-red-600">{{ $errors->first() }}</p>
                @endif
            </div>

            @if(!$generated)
            {{-- Invite à sélectionner les dates --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-12 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Sélectionnez une plage de dates et cliquez sur <strong>Générer le rapport</strong>.</p>
            </div>

            @else

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- RAPPORT IMPRIMABLE                                              --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}

            {{-- En-tête du rapport --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">KAYPA — Rapport Financier</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Comptes Courants</p>
                    </div>
                    <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
                        <p>Par : {{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg inline-flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="font-semibold text-blue-800 dark:text-blue-200">
                        Période : {{ $dateDebut->format('d/m/Y') }} → {{ $dateFin->format('d/m/Y') }}
                    </span>
                    <span class="text-blue-600 dark:text-blue-400 text-sm">
                        ({{ (int) $dateDebut->copy()->startOfDay()->diffInDays($dateFin->copy()->startOfDay()) + 1 }} jour(s))
                    </span>
                </div>
            </div>

            {{-- ── KPI résumé ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 border-green-500">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total dépôts</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($flux['depots'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $flux['depots_nb'] }} opération(s) · HTG</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 border-red-500">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total retraits</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($flux['retraits'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $flux['retraits_nb'] }} opération(s) · HTG</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 {{ $flux['net'] >= 0 ? 'border-blue-500' : 'border-orange-500' }}">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Flux net</p>
                    <p class="text-2xl font-bold {{ $flux['net'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }} mt-1">
                        {{ $flux['net'] >= 0 ? '+' : '' }}{{ number_format($flux['net'], 2) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">HTG</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 border-l-4 border-purple-500">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Revenus frais</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ number_format($flux['revenus_frais'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">HTG</p>
                </div>
            </div>

            {{-- ── Tableau récapitulatif complet ── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Récapitulatif des opérations</h3>
                </div>
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type d'opération</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nb d'opérations</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant total (HTG)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr class="bg-green-50 dark:bg-green-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-green-800 dark:text-green-200">Dépôts</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($flux['depots_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-green-700 dark:text-green-300">+ {{ number_format($flux['depots'], 2) }}</td>
                        </tr>
                        <tr class="bg-red-50 dark:bg-red-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-red-800 dark:text-red-200">Retraits</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($flux['retraits_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-red-700 dark:text-red-300">- {{ number_format($flux['retraits'], 2) }}</td>
                        </tr>
                        <tr class="bg-purple-50 dark:bg-purple-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-purple-800 dark:text-purple-200">Frais d'ouverture</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($flux['frais_ouverture_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-purple-700 dark:text-purple-300">{{ number_format($flux['frais_ouverture'], 2) }}</td>
                        </tr>
                        <tr class="bg-yellow-50 dark:bg-yellow-900/20">
                            <td class="px-6 py-3 text-sm font-medium text-yellow-800 dark:text-yellow-200">Frais de service mensuel</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($flux['frais_service_nb']) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-yellow-700 dark:text-yellow-300">{{ number_format($flux['frais_service'], 2) }}</td>
                        </tr>
                        <tr class="bg-gray-100 dark:bg-gray-700 font-semibold">
                            <td class="px-6 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">TOTAL opérations</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($flux['total_ops']) }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-gray-100">—</td>
                        </tr>
                        <tr class="bg-blue-50 dark:bg-blue-900/30">
                            <td class="px-6 py-3 text-sm font-bold text-blue-900 dark:text-blue-100">FLUX NET (dépôts − retraits)</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-500">—</td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-blue-700 dark:text-blue-300 text-base">
                                {{ $flux['net'] >= 0 ? '+' : '' }}{{ number_format($flux['net'], 2) }}
                            </td>
                        </tr>
                        <tr class="bg-teal-50 dark:bg-teal-900/30">
                            <td class="px-6 py-3 text-sm font-bold text-teal-900 dark:text-teal-100">REVENUS FRAIS (ouverture + service)</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-500">—</td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-teal-700 dark:text-teal-300 text-base">
                                {{ number_format($flux['revenus_frais'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ── Détail journalier ── --}}
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
                                <th class="px-5 py-3 text-right text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">Fr. Ouv.</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">Fr. Serv.</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Flux net</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            @php
                                $totJourDepot   = 0; $totJourRetrait = 0;
                                $totJourFraisO  = 0; $totJourFraisS  = 0;
                            @endphp
                            @foreach($joursDetail as $jour => $lignes)
                            @php
                                $d  = (float) $lignes->firstWhere('type', 'DEPOT')?->total          ?? 0;
                                $r  = (float) $lignes->firstWhere('type', 'RETRAIT')?->total         ?? 0;
                                $fo = (float) $lignes->firstWhere('type', 'FRAIS_OUVERTURE')?->total ?? 0;
                                $fs = (float) $lignes->firstWhere('type', 'FRAIS_SERVICE')?->total   ?? 0;
                                $net = $d - $r;
                                $totJourDepot   += $d;  $totJourRetrait += $r;
                                $totJourFraisO  += $fo; $totJourFraisS  += $fs;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-5 py-2.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($jour)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-2.5 whitespace-nowrap text-sm text-right {{ $d > 0 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-400' }}">
                                    {{ $d > 0 ? number_format($d, 2) : '—' }}
                                </td>
                                <td class="px-5 py-2.5 whitespace-nowrap text-sm text-right {{ $r > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-400' }}">
                                    {{ $r > 0 ? number_format($r, 2) : '—' }}
                                </td>
                                <td class="px-5 py-2.5 whitespace-nowrap text-sm text-right {{ $fo > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400' }}">
                                    {{ $fo > 0 ? number_format($fo, 2) : '—' }}
                                </td>
                                <td class="px-5 py-2.5 whitespace-nowrap text-sm text-right {{ $fs > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-400' }}">
                                    {{ $fs > 0 ? number_format($fs, 2) : '—' }}
                                </td>
                                <td class="px-5 py-2.5 whitespace-nowrap text-sm text-right font-semibold {{ $net >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                                </td>
                            </tr>
                            @endforeach
                            {{-- Ligne totaux --}}
                            <tr class="bg-gray-100 dark:bg-gray-700 font-semibold border-t-2 border-gray-300 dark:border-gray-500">
                                <td class="px-5 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">TOTAL</td>
                                <td class="px-5 py-3 text-sm text-right text-green-700 dark:text-green-300 font-bold">{{ number_format($totJourDepot, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-right text-red-700 dark:text-red-300 font-bold">{{ number_format($totJourRetrait, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-right text-purple-700 dark:text-purple-300 font-bold">{{ number_format($totJourFraisO, 2) }}</td>
                                <td class="px-5 py-3 text-sm text-right text-yellow-700 dark:text-yellow-300 font-bold">{{ number_format($totJourFraisS, 2) }}</td>
                                @php $netTotal = $totJourDepot - $totJourRetrait; @endphp
                                <td class="px-5 py-3 text-sm text-right font-bold {{ $netTotal >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }}">
                                    {{ $netTotal >= 0 ? '+' : '' }}{{ number_format($netTotal, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ── Par succursale ── --}}
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
                            <th class="px-5 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Net</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($parBranche as $row)
                        @php $net = $row->depot_periode - $row->retrait_periode; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row->branche }}</td>
                            <td class="px-5 py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ number_format($row->nb_actif) }}</td>
                            <td class="px-5 py-3 text-sm text-right font-semibold text-blue-600 dark:text-blue-400">{{ number_format($row->solde_total, 2) }} HTG</td>
                            <td class="px-5 py-3 text-sm text-right text-green-600 dark:text-green-400">
                                +{{ number_format($row->depot_periode, 2) }}
                                <span class="text-xs text-gray-400">({{ $row->depot_nb }})</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-right text-red-600 dark:text-red-400">
                                -{{ number_format($row->retrait_periode, 2) }}
                                <span class="text-xs text-gray-400">({{ $row->retrait_nb }})</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-right font-semibold {{ $net >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Aucune donnée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ── Statistiques compte ── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Informations complémentaires</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Comptes ouverts sur la période</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($ouvertures) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Solde total (comptes actifs)</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($soldeFin, 2) }} HTG</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Total opérations enregistrées</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($flux['total_ops']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Pied de page rapport --}}
            <div class="text-center text-xs text-gray-400 dark:text-gray-500 py-2">
                Rapport généré automatiquement par KAYPA — {{ now()->format('d/m/Y H:i') }}
                · Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
            </div>

            @endif

        </div>
    </div>
</x-app-layout>
