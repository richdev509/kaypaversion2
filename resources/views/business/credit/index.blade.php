<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Crédits Business</h2>
            <a href="{{ route('business.credit.plans') }}" class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Plans de taux
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
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actifs</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['active'] }}</p>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nom ou N° business..."
                        class="flex-1 min-w-48 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <select name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                        <option value="">Tous statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expiré</option>
                        <option value="closed" @selected(request('status') === 'closed')>Clôturé</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">Filtrer</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('business.credit.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg">Effacer</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Business</th>
                            <th class="px-4 py-3 text-right">Limite</th>
                            <th class="px-4 py-3 text-right">Utilisé</th>
                            <th class="px-4 py-3 text-left">Plan</th>
                            <th class="px-4 py-3 text-left">Durée</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($credits as $credit)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $credit->business->name }}</p>
                                <p class="text-xs font-mono text-gray-400">{{ $credit->business->business_number }}</p>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($credit->approved_limit, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-orange-600 dark:text-orange-400">
                                {{ number_format($credit->credit_used, 2) }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                {{ $credit->ratePlan?->name ?? 'Manuel' }}
                                <span class="text-indigo-600 dark:text-indigo-400">{{ $credit->getEffectiveTaux() }}%/mois</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $credit->duration_months }} mois
                                @if($credit->expires_at)
                                    <br>exp. {{ $credit->expires_at->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($credit->isPending())
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full">Pending</span>
                                @elseif($credit->isActive())
                                    <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Actif</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full">{{ $credit->getStatusLabel() }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('business.credit.show', $credit) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Détail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun crédit trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($credits->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $credits->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
