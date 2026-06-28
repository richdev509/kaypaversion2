<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Entreprises Business
            </h2>
            <a href="{{ route('business.entities.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Nouveau Business
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">KYC Vérifiés</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['kyc_verified'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">KYC Pending</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $stats['kyc_pending'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actifs</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $stats['active'] }}</p>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 mb-6">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Numéro, nom, secteur..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <select name="status_kyc" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                            <option value="">KYC — tous</option>
                            <option value="pending" @selected(request('status_kyc') === 'pending')>Pending</option>
                            <option value="verified" @selected(request('status_kyc') === 'verified')>Vérifié</option>
                            <option value="rejected" @selected(request('status_kyc') === 'rejected')>Rejeté</option>
                        </select>
                    </div>
                    <div>
                        <select name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                            <option value="">Statut — tous</option>
                            <option value="active" @selected(request('status') === 'active')>Actif</option>
                            <option value="suspended" @selected(request('status') === 'suspended')>Suspendu</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">Filtrer</button>
                    @if(request()->hasAny(['search', 'status_kyc', 'status']))
                        <a href="{{ route('business.entities.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg transition">Effacer</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">N° Business</th>
                            <th class="px-4 py-3 text-left">Nom</th>
                            <th class="px-4 py-3 text-left">Propriétaire</th>
                            <th class="px-4 py-3 text-left">KYC</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">KCB</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($entities as $entity)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-3 font-mono text-indigo-600 dark:text-indigo-400 font-medium">
                                {{ $entity->business_number }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $entity->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $entity->ownerClient?->first_name }} {{ $entity->ownerClient?->last_name }}
                            </td>
                            <td class="px-4 py-3">
                                @if($entity->status_kyc === 'verified')
                                    <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Vérifié</span>
                                @elseif($entity->status_kyc === 'pending')
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full">Pending</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full">{{ $entity->status_kyc }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($entity->status === 'active')
                                    <span class="px-2 py-0.5 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full">Actif</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full">{{ $entity->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $entity->currentAccount?->account_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('business.entities.show', $entity) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Voir</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Aucun business trouvé.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($entities->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $entities->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
