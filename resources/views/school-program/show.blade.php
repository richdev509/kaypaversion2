<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('school-programs.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ $program->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Coupon valide du {{ $program->date_debut->format('d/m/Y') }} au {{ $program->date_fin->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($program->isActive())
                    <span class="px-3 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full font-medium">Actif</span>
                @else
                    <span class="px-3 py-1 text-xs bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-full font-medium">Archivé</span>
                @endif
                @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('school-programs.edit', $program) }}"
                   class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Modifier
                </a>
                @if($program->isActive())
                <form method="POST" action="{{ route('school-programs.destroy', $program) }}"
                      onsubmit="return confirm('Archiver ce programme ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-sm bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-800 transition">
                        Archiver
                    </button>
                </form>
                @endif
                @endif
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
            @if(session('bulk_errors') && count(session('bulk_errors')) > 0)
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">Erreurs lors de l'inscription en masse :</p>
                    <ul class="text-xs text-yellow-700 dark:text-yellow-300 space-y-1">
                        @foreach(session('bulk_errors') as $err)
                            <li>• {{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Infos programme --}}
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Détails du programme</h3>
                    <dl class="text-sm space-y-2">
                        @if($program->description)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="text-gray-700 dark:text-gray-300 mt-0.5">{{ $program->description }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Inscriptions ouvertes</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $program->inscription_debut->format('d/m/Y') }} → {{ $program->inscription_fin->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Montant bloqué</dt>
                            <dd class="font-medium text-orange-600 dark:text-orange-400">{{ number_format($program->montant_blocage, 2) }} GDS pendant {{ $program->duree_blocage_jours }} jours</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Créé par</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $program->creator?->name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Configuration des tiers</h3>
                    <div class="space-y-4">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Tier 1 — Standard</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Épargne ≥ <strong>{{ number_format($program->tier1_seuil, 0) }} GDS</strong></p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">Coupon {{ number_format($program->tier1_coupon, 0) }} GDS</p>
                        </div>
                        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-1">Tier 2 — Premium</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">Épargne ≥ <strong>{{ number_format($program->tier2_seuil, 0) }} GDS</strong></p>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Coupon {{ number_format($program->tier2_coupon, 0) }} GDS</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clients éligibles --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Clients éligibles non encore inscrits</h3>
                        <p class="text-sm text-gray-400 mt-0.5">{{ $eligibleClients->count() }} client(s)</p>
                    </div>
                    @if(auth()->user()->hasRole('admin') && $eligibleClients->count() > 0)
                    <form method="POST" action="{{ route('school-programs.bulk-enroll', $program) }}"
                          onsubmit="return confirm('Inscrire tous les {{ $eligibleClients->count() }} clients éligibles ? Cette action est irréversible.')">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
                            Inscrire tous ({{ $eligibleClients->count() }})
                        </button>
                    </form>
                    @endif
                </div>

                @if($eligibleClients->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">
                        Aucun client éligible non encore inscrit.
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Client</th>
                                <th class="px-4 py-3 text-left">N° KCE</th>
                                <th class="px-4 py-3 text-right">Solde épargne</th>
                                <th class="px-4 py-3 text-center">Tier</th>
                                <th class="px-4 py-3 text-left">Coupon estimé</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($eligibleClients as $client)
                            @php
                                $kce = $client->activeSavingsAccount;
                                $tier = $kce && $kce->balance >= $program->tier2_seuil ? 2 : 1;
                                $couponVal = $tier === 2 ? $program->tier2_coupon : $program->tier1_coupon;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $client->first_name }} {{ $client->last_name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $client->client_id }}</p>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                    {{ $kce?->account_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                    {{ number_format($kce?->balance ?? 0, 2) }} GDS
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 text-xs {{ $tier === 2 ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' }} rounded-full">
                                        Tier {{ $tier }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-green-600 dark:text-green-400">
                                    {{ number_format($couponVal, 0) }} GDS
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('school-programs.enroll', [$program, $client]) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded transition">
                                            Inscrire
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Inscriptions --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Inscriptions</h3>
                    <span class="text-sm text-gray-400">{{ $enrollments->total() }} total</span>
                </div>

                @if($enrollments->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">Aucune inscription pour ce programme.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Client</th>
                                <th class="px-4 py-3 text-left">Code coupon</th>
                                <th class="px-4 py-3 text-center">Tier</th>
                                <th class="px-4 py-3 text-right">Valeur</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-left">Déblocage</th>
                                <th class="px-4 py-3 text-left">Partenaire</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($enrollments as $enrollment)
                            @php
                                $statusColors = [
                                    'active'    => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
                                    'used'      => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
                                    'expired'   => 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-400',
                                    'cancelled' => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $enrollment->client?->first_name }} {{ $enrollment->client?->last_name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $enrollment->client?->client_id }}</p>
                                </td>
                                <td class="px-4 py-3 font-mono text-indigo-600 dark:text-indigo-400 font-medium tracking-wider">
                                    {{ $enrollment->coupon_code }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 text-xs {{ $enrollment->tier === 2 ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' }} rounded-full">
                                        T{{ $enrollment->tier }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">
                                    {{ number_format($enrollment->coupon_value, 0) }} GDS
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs {{ $statusColors[$enrollment->coupon_status] ?? '' }} rounded-full">
                                        {{ $enrollment->getStatusLabel() }}
                                    </span>
                                    @if($enrollment->coupon_status === 'used' && $enrollment->used_at)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $enrollment->used_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $enrollment->blocked_until?->format('d/m/Y') ?? '—' }}
                                    @if($enrollment->isBlockExpired())
                                        <span class="text-green-500 dark:text-green-400 ml-1">✓</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $enrollment->usedByAffiliate ? ($enrollment->usedByAffiliate->nom . ' ' . $enrollment->usedByAffiliate->prenom) : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($enrollments->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $enrollments->links() }}
                </div>
                @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
