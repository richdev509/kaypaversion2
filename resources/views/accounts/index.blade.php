<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Liste des Comptes
            </h2>
            <a href="{{ route('accounts.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                + Nouveau Compte
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistiques en grille (comme dashboard) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Card Total Comptes -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Comptes</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">
                                    {{ $accounts->total() }}
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Comptes Actifs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Comptes Actifs</p>
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">
                                    {{ $accounts->where('status', 'actif')->count() }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Avec Dette -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avec Dette</p>
                                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-2">
                                    {{ $accounts->filter(fn($a) => $a->retrait_status == 1)->count() }}
                                </p>
                            </div>
                            <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                                <svg class="w-8 h-8 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Solde Total -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    @if(Auth::user()->role == 'admin' || Auth::user()->role == 'comptable')
                                        Solde Total (Toutes succursales)
                                    @else
                                        Solde Total ({{ Auth::user()->branch->name ?? 'Ma succursale' }})
                                    @endif
                                </p>
                                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">
                                    {{ number_format($totalBalance ?? 0, 0) }} HTG
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->isManager() && !request()->filled('search') && !request()->filled('status') && !request()->filled('has_debt'))
            <!-- Message pour les managers sans recherche -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 text-center">
                <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-blue-500 dark:text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">
                        🔍 Utilisez la recherche pour afficher les comptes
                    </h3>
                    <p class="text-sm text-blue-600 dark:text-blue-300">
                        Pour consulter les comptes, veuillez effectuer une recherche par numéro de compte, nom de client ou utiliser les filtres ci-dessus.
                    </p>
                </div>
            </div>
            @endif

            <!-- Recherche et Filtres -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">🔍 Rechercher un Compte</h3>
                    <form action="{{ route('accounts.index') }}" method="GET" id="searchForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Rechercher par numéro de compte, nom du client..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                oninput="document.getElementById('searchForm').submit()"
                            >
                        </div>

                        <div>
                            <select
                                id="status"
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                onchange="document.getElementById('searchForm').submit()"
                            >
                                <option value="">Tous les statuts</option>
                                <option value="actif" {{ request('status') === 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="clos" {{ request('status') === 'clos' ? 'selected' : '' }}>Clos</option>
                                <option value="suspendu" {{ request('status') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                            </select>
                        </div>

                        <div>
                            <select
                                id="has_debt"
                                name="has_debt"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                onchange="document.getElementById('searchForm').submit()"
                            >
                                <option value="">Tous (Dette)</option>
                                <option value="1" {{ request('has_debt') === '1' ? 'selected' : '' }}>Avec dette</option>
                                <option value="0" {{ request('has_debt') === '0' ? 'selected' : '' }}>Sans dette</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des comptes -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    N° Compte
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Client
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Solde
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Date début
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($accounts as $account)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('accounts.show', $account) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                            {{ $account->account_id ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('clients.show', $account->client) }}" class="text-sm text-gray-900 dark:text-gray-100 hover:text-blue-600">
                                            {{ $account->client->full_name }}
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $account->client->phone }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $account->plan->name ?? 'N/A' }}
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $account->plan->duree ?? 0 }} jours
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ number_format($account->solde_virtuel, 2) }} HTG
                                        </div>
                                        @if($account->hasDebt())
                                            <div class="text-xs text-red-600 dark:text-red-400">
                                                Dette: {{ number_format($account->withdraw, 2) }} HTG
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($account->status === 'actif')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Actif
                                            </span>
                                        @elseif($account->status === 'clos')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Clos
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Suspendu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $account->date_debut->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('accounts.show', $account) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucun compte</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Commencez par créer un nouveau compte.</p>
                                        <div class="mt-6">
                                            <a href="{{ route('accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                                + Nouveau Compte
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($accounts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $accounts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
