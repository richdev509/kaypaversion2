<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💸 Rechercher un Transfert
            </h2>
            @if(Auth::user()->hasAnyRole(['admin', 'agent', 'manager']))
            <a href="{{ route('transfers.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                + Nouveau Transfert
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
            <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
            @endif

            <!-- Carte de recherche -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 dark:bg-blue-900 rounded-full mb-4">
                            <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            Rechercher un Transfert
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Entrez le numéro de transfert à 10 chiffres
                        </p>
                    </div>

                    <form method="GET" action="{{ route('transfers.index') }}" class="space-y-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Numéro de Transfert
                            </label>
                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ $search ?? '' }}"
                                placeholder="Ex: 1234567890"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                class="w-full px-4 py-3 text-center text-2xl font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                required
                                autofocus
                            >
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                                Le numéro doit contenir exactement 10 chiffres
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="w-full px-6 py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-semibold rounded-lg transition shadow-lg hover:shadow-xl"
                        >
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </form>
                </div>
            </div>

            <!-- Résultat de la recherche -->
            @if($search && $transfer)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-500 to-green-600">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-check-circle"></i> Transfert Trouvé
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Numéro de Transfert</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $transfer->transfer_number }}</p>
                            </div>
                            <div>
                                {!! $transfer->status_badge !!}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Expéditeur</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $transfer->sender_name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $transfer->sender_country_code }} {{ $transfer->sender_phone }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Bénéficiaire</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $transfer->receiver_name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Montant</p>
                                <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($transfer->amount, 0) }} GDS</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Date de création</p>
                                <p class="text-gray-900 dark:text-white">{{ $transfer->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <a href="{{ route('transfers.show', $transfer) }}" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-center font-medium rounded-lg transition">
                                <i class="fas fa-eye"></i> Voir Détails
                            </a>
                            @if($transfer->status === 'pending')
                            <a href="{{ route('transfers.pay', $transfer) }}" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-center font-medium rounded-lg transition">
                                <i class="fas fa-hand-holding-usd"></i> Payer
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @elseif($search && !$transfer)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-2 border-red-200 dark:border-red-800">
                <div class="p-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full mb-4">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        Aucun transfert trouvé
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Le numéro de transfert <strong>{{ $search }}</strong> n'existe pas dans le système.
                    </p>
                </div>
            </div>
            @endif

            <!-- Lien vers liste complète pour admin/manager -->
            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <div class="mt-6 text-center">
                <a href="{{ route('transfers.all') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-list mr-2"></i> Voir la liste complète des transferts
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
