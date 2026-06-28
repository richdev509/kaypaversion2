<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('business.payroll.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 font-mono">{{ $batch->reference }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $batch->business->name }}</p>
                </div>
            </div>
            @if($batch->isPendingApproval())
            <form method="POST" action="{{ route('business.payroll.approve', $batch) }}" class="flex gap-2 items-center">
                @csrf
                <input type="text" name="note" placeholder="Note (optionnel)"
                    class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-1.5">
                <button type="submit" onclick="return confirm('Approuver ce batch payroll ?')"
                    class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    Approuver
                </button>
            </form>
            @endif
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

            <!-- Résumé -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Statut</p>
                    <p class="text-lg font-bold mt-1
                        {{ $batch->isPendingApproval() ? 'text-yellow-600 dark:text-yellow-400' :
                           ($batch->isCompleted() ? 'text-green-600 dark:text-green-400' :
                           ($batch->status === 'failed' ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400')) }}">
                        {{ $batch->getStatusLabel() }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Employés</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $batch->employee_count ?? $batch->items->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Montant total</p>
                    <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($batch->total_amount, 2) }} HTG</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Période</p>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">{{ $batch->period_label ?? '—' }}</p>
                </div>
            </div>

            <!-- Meta -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                <dl class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Créé le</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $batch->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Par</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $batch->creator?->name ?? '—' }}</dd>
                    </div>
                    @if($batch->approvedBy)
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Approuvé par</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $batch->approvedBy->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Approuvé le</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $batch->approved_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
                @if($batch->note)
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 italic">{{ $batch->note }}</p>
                @endif
            </div>

            <!-- Items -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Détail par employé</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Employé</th>
                            <th class="px-4 py-3 text-left">Compte destination</th>
                            <th class="px-4 py-3 text-right">Montant</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Transaction ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($batch->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                {{ $item->employee?->full_name ?? '—' }}
                                @if($item->employee?->poste)
                                    <span class="text-xs text-gray-400 ml-1">{{ $item->employee->poste }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $item->destination_account }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($item->amount, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                @if($item->isSuccess())
                                    <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Succès</span>
                                @elseif($item->isFailed())
                                    <span class="px-2 py-0.5 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full">Échec</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-full">{{ $item->getStatusLabel() }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-400">
                                {{ $item->transaction_id ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucun item.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
