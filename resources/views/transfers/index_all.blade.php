<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💸 Liste Complète des Transferts
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('transfers.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-search"></i> Recherche Simple
                </a>
                @if(Auth::user()->hasAnyRole(['admin', 'agent', 'manager']))
                <a href="{{ route('transfers.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    + Nouveau Transfert
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            @if(Auth::user()->isAgent())
            <!-- Message pour les agents -->
            <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <i class="fas fa-info-circle"></i>
                    Vous consultez uniquement les transferts <strong>payés</strong> ou <strong>annulés</strong> de votre branche <strong>{{ Auth::user()->branch->name }}</strong>.
                </p>
            </div>
            @elseif(!Auth::user()->isAdmin() && Auth::user()->branch)
            <!-- Message pour les managers -->
            <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <i class="fas fa-info-circle"></i>
                    <strong>Information :</strong> Vous consultez uniquement les transferts de votre branche : <strong>{{ Auth::user()->branch->name }}</strong>
                </p>
            </div>
            @endif

            <!-- Statistiques et Filtres sur la même ligne (Admin et Manager uniquement) -->
            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                <!-- Statistiques à gauche (4 colonnes) -->
                <div class="lg:col-span-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📊 Statistiques</h3>
                            <div class="space-y-4">
                                <!-- Total Transferts -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                            <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Total</span>
                                    </div>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</span>
                                </div>

                                <!-- En Attente -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                                            <svg class="h-4 w-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">En Attente</span>
                                    </div>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['pending']) }}</span>
                                </div>

                                <!-- Montant Payé -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                                            <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Montant</span>
                                    </div>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_amount'], 0) }} GDS</span>
                                </div>

                                <!-- Frais Perçus (Admin uniquement) -->
                                @if(Auth::user()->isAdmin())
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                                            <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Frais</span>
                                    </div>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_fees'], 0) }} GDS</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtres à droite (8 colonnes) -->
                <div class="lg:col-span-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🔍 Filtres</h3>
                            <form method="GET" action="{{ route('transfers.all') }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                            <option value="">Tous</option>
                                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>En attente</option>
                                            <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Payé</option>
                                            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Début</label>
                                        <input type="date" name="date_debut" value="{{ $dateDebut }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Fin</label>
                                        <input type="date" name="date_fin" value="{{ $dateFin }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    </div>

                                    <div class="flex items-end">
                                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            Filtrer
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recherche</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="search" value="{{ $search }}" placeholder="Numéro, nom, téléphone..." class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('transfers.all') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition whitespace-nowrap">
                                            Réinitialiser
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Filtres (Tous les utilisateurs y compris agents) -->
            @if(Auth::user()->isAgent())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🔍 Filtres</h3>
                    <form method="GET" action="{{ route('transfers.all') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">Tous</option>
                                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Payé</option>
                                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Début</label>
                                <input type="date" name="date_debut" value="{{ $dateDebut }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Fin</label>
                                <input type="date" name="date_fin" value="{{ $dateFin }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Filtrer
                                </button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recherche</label>
                            <div class="flex gap-2">
                                <input type="text" name="search" value="{{ $search }}" placeholder="Numéro, nom, téléphone..." class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <a href="{{ route('transfers.all') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition whitespace-nowrap">
                                    Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Liste des transferts -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($transfers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Numéro</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expéditeur</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bénéficiaire</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($transfers as $transfer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('transfers.show', $transfer) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                            {{ $transfer->transfer_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $transfer->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $transfer->sender_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $transfer->sender_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $transfer->receiver_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $transfer->receiver_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($transfer->amount, 0) }} GDS</div>
                                        @if(Auth::user()->isAdmin())
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Frais: {{ number_format($transfer->fees, 0) }} GDS</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            @if($transfer->status === 'pending') bg-yellow-500 text-white
                                            @elseif($transfer->status === 'paid') bg-green-600 text-white
                                            @elseif($transfer->status === 'cancelled') bg-red-600 text-white
                                            @else bg-gray-500 text-white
                                            @endif">
                                            {{ $transfer->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('transfers.show', $transfer) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400" title="Détails">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            @if($transfer->status === 'pending')
                                                <a href="{{ route('transfers.pay', $transfer) }}" class="text-green-600 hover:text-green-900 dark:text-green-400" title="Payer">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            @if($transfer->status === 'paid')
                                                <a href="{{ route('transfers.receipt-receiver', $transfer) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400" title="Reçu" target="_blank">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transfers->links() }}
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucun transfert</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aucun transfert trouvé pour cette période</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
