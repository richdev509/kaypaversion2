<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Payroll Business</h2>
            <a href="{{ route('business.payroll.employees') }}" class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Employés
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">En attente d'approbation</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $stats['pending_approval'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Complétés</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['completed'] }}</p>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Référence ou nom business..."
                        class="flex-1 min-w-48 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                    <select name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                        <option value="">Tous statuts</option>
                        <option value="draft" @selected(request('status') === 'draft')>Brouillon</option>
                        <option value="pending_approval" @selected(request('status') === 'pending_approval')>En attente</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approuvé</option>
                        <option value="completed" @selected(request('status') === 'completed')>Complété</option>
                        <option value="failed" @selected(request('status') === 'failed')>Échec</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">Filtrer</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('business.payroll.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg">Effacer</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Référence</th>
                            <th class="px-4 py-3 text-left">Business</th>
                            <th class="px-4 py-3 text-left">Période</th>
                            <th class="px-4 py-3 text-center">Employés</th>
                            <th class="px-4 py-3 text-right">Montant total</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($batches as $batch)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-mono text-indigo-600 dark:text-indigo-400 font-medium">
                                {{ $batch->reference }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $batch->business->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $batch->period_label ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $batch->employee_count ?? 0 }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($batch->total_amount, 2) }} HTG
                            </td>
                            <td class="px-4 py-3">
                                @if($batch->isPendingApproval())
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full">En attente</span>
                                @elseif($batch->isCompleted())
                                    <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Complété</span>
                                @elseif($batch->status === 'failed')
                                    <span class="px-2 py-0.5 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full">Échec</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full">{{ $batch->getStatusLabel() }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('business.payroll.show', $batch) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Détail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun batch payroll trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($batches->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $batches->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
