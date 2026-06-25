<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Comptes Courants
            </h2>
            <div class="flex gap-2">
                @if($isAdmin)
                <a href="{{ route('current-accounts.settings') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    ⚙️ Paramètres
                </a>
                @endif
                <a href="{{ route('current-accounts.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    + Ouvrir un compte
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg">
                {{ session('error') }}
            </div>
            @endif

            {{-- Statistiques : admin/comptable uniquement --}}
            @if($isAdmin)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total comptes</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ number_format($totalAccounts) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Comptes actifs</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ number_format($activeAccounts) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Solde total (actifs)</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ number_format($totalBalance, 2) }} HTG</p>
                </div>
            </div>
            @endif

            {{-- Barre de recherche --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    @if($isAgent)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Recherchez un compte par numéro, nom du client ou téléphone.</p>
                    @endif
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-48">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="N° compte, nom, téléphone..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        @if($isAdmin)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
                            <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm">
                                <option value="">Tous</option>
                                <option value="actif"    @selected(request('status') == 'actif')>Actif</option>
                                <option value="suspendu" @selected(request('status') == 'suspendu')>Suspendu</option>
                                <option value="cloture"  @selected(request('status') == 'cloture')>Clôturé</option>
                            </select>
                        </div>
                        @endif
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                            Rechercher
                        </button>
                        @if(request()->hasAny(['search','status']))
                        <a href="{{ route('current-accounts.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm">
                            Réinitialiser
                        </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Message d'invite pour agent sans recherche --}}
            @if($isAgent && !request()->filled('search'))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Utilisez la recherche ci-dessus pour trouver un compte courant.</p>
                    <a href="{{ route('current-accounts.create') }}" class="mt-4 inline-block px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                        + Ouvrir un nouveau compte
                    </a>
                </div>
            </div>

            @else
            {{-- Table des comptes --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">N° Compte</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solde</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                                @if($isAdmin)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ouverture</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($accounts as $account)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900 dark:text-gray-100">
                                    {{ $account->account_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $account->client?->full_name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $account->client?->phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($account->balance, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badge = match($account->status) {
                                            'actif'    => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'suspendu' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'cloture'  => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            default    => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                                @if($isAdmin)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $account->created_at->format('d/m/Y') }}
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-3">
                                        <a href="{{ route('current-accounts.show', $account) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            Voir
                                        </a>
                                        @if($account->status === 'actif')
                                        <a href="{{ route('current-accounts.deposit.form', $account) }}" class="text-green-600 hover:text-green-800 dark:text-green-400">
                                            Dépôt
                                        </a>
                                        <a href="{{ route('current-accounts.withdraw.form', $account) }}" class="text-orange-600 hover:text-orange-800 dark:text-orange-400">
                                            Retrait
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 6 : 5 }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    @if($isAgent)
                                        Aucun compte trouvé pour cette recherche.
                                    @else
                                        Aucun compte courant trouvé.
                                    @endif
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
            @endif

        </div>
    </div>
</x-app-layout>
