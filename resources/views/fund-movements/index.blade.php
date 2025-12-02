<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💰 Gestion Financière
            </h2>
            @if(Auth::user()->hasPermissionTo('fund-movements.create'))
            <a href="{{ route('fund-movements.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                ➕ Nouveau mouvement
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('fund-movements.index') }}" class="flex flex-wrap gap-3 items-end">
                        <!-- Statut -->
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                            <select name="status" class="w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Tous</option>
                                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>En attente</option>
                                <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approuvé</option>
                                <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejeté</option>
                            </select>
                        </div>

                        <!-- Type -->
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select name="type" class="w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Tous</option>
                                <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Entrée</option>
                                <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Sortie</option>
                            </select>
                        </div>

                        @if(Auth::user()->hasRole('admin'))
                        <!-- Branche -->
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Branche</label>
                            <select name="branch_id" class="w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Toutes</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Date de début -->
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Du</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        </div>

                        <!-- Date de fin -->
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Au</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                   class="w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        </div>

                        <!-- Bouton filtrer -->
                        <div>
                            <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-md transition">
                                🔍 Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des mouvements -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($movements->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Référence</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Destination</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($movements as $movement)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $movement->reference }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {!! $movement->type_badge !!}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ number_format($movement->amount, 2) }} HTG
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                @if($movement->source_type === 'SUCCURSALE')
                                                    {{ $movement->sourceBranch->name ?? 'N/A' }}
                                                @else
                                                    {{ $movement->external_source ?? $movement->source_type }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $movement->destinationBranch->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {!! $movement->status_badge !!}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                                {{ $movement->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('fund-movements.show', $movement) }}"
                                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                    👁️ Voir
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $movements->links() }}
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                            Aucun mouvement de fonds trouvé.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
