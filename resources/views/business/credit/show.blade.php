<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.credit.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Crédit — {{ $credit->business->name }}</h2>
                <p class="text-sm text-gray-500 font-mono">{{ $credit->business->business_number }}</p>
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

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Détails crédit -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Détails du Crédit</h3>
                        @if($credit->isPending())
                            <span class="px-2 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full">En attente</span>
                        @elseif($credit->isActive())
                            <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Actif</span>
                        @else
                            <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-full">{{ $credit->getStatusLabel() }}</span>
                        @endif
                    </div>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Limite approuvée</dt>
                            <dd class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ number_format($credit->approved_limit, 2) }} HTG</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Utilisé</dt>
                            <dd class="font-bold text-lg text-orange-600 dark:text-orange-400">{{ number_format($credit->credit_used, 2) }} HTG</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Disponible</dt>
                            <dd class="font-medium text-green-600 dark:text-green-400">{{ number_format($credit->getAvailableCredit(), 2) }} HTG</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Taux mensuel</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $credit->getEffectiveTaux() }}%</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Plan</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $credit->ratePlan?->name ?? 'Taux manuel' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Durée</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $credit->duration_months }} mois</dd>
                        </div>
                        @if($credit->starts_at)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Début</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $credit->starts_at->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Expiration</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $credit->expires_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        @endif
                        @if($credit->approvedBy)
                        <div class="col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">Approuvé par</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $credit->approvedBy->name }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Actions -->
                <div class="space-y-4">
                    @if($credit->isPending())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Approbation</h4>
                        <form method="POST" action="{{ route('business.credit.approve', $credit) }}">
                            @csrf
                            <textarea name="note" rows="2" placeholder="Note (optionnel)"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2 mb-3"></textarea>
                            <button type="submit" onclick="return confirm('Approuver ce crédit ?')"
                                class="w-full px-3 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-semibold">
                                Approuver
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($credit->isActive())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Calculer Intérêts</h4>
                        <form method="POST" action="{{ route('business.credit.interest', $credit) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400">Période du</label>
                                <input type="date" name="period_start" required class="w-full rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-2 py-1.5 mt-0.5">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400">Au</label>
                                <input type="date" name="period_end" required class="w-full rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-2 py-1.5 mt-0.5">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 dark:text-gray-400">Solde moyen utilisé (HTG)</label>
                                <input type="number" name="avg_balance_used" min="0" step="0.01"
                                    class="w-full rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-2 py-1.5 mt-0.5">
                            </div>
                            <button type="submit" class="w-full px-3 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                                Calculer
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Intérêts -->
            @if($credit->interestCharges->count())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Charges d'intérêts</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Période</th>
                            <th class="px-4 py-3 text-right">Solde moyen</th>
                            <th class="px-4 py-3 text-right">Taux</th>
                            <th class="px-4 py-3 text-right">Total dû</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($credit->interestCharges as $charge)
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                {{ $charge->period_start->format('d/m/Y') }} → {{ $charge->period_end->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($charge->avg_balance_used, 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ $charge->taux_applied }}%</td>
                            <td class="px-4 py-3 text-right font-medium text-orange-600 dark:text-orange-400">{{ number_format($charge->total_due, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs {{ $charge->status === 'debited' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300' }} rounded-full">
                                    {{ $charge->getStatusLabel() }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Journal d'actions -->
            @if($credit->actionLogs->count())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Journal d'actions</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($credit->actionLogs->sortByDesc('created_at') as $log)
                    <div class="px-6 py-3 flex gap-4">
                        <div class="text-xs text-gray-400 whitespace-nowrap mt-0.5 w-32">{{ $log->created_at?->format('d/m/Y H:i') }}</div>
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $log->getActionLabel() }}</span>
                            @if($log->note)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $log->note }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-0.5">par {{ $log->doneBy?->name ?? '—' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
