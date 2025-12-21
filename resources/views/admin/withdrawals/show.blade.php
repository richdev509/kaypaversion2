<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💰 {{ __('Détails de la Demande de Retrait') }} - {{ $request->reference_id }}
            </h2>
            <a href="{{ route('admin.withdrawals.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Messages de succès/erreur -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-300" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Informations principales -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Statut et Référence -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $request->reference_id }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Créée le {{ $request->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $request->getStatusBadgeClass() }}">
                                    {{ $request->getStatusLabel() }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-6">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Montant</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($request->amount, 2) }} GDS</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Méthode de retrait</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $request->getMethodLabel() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations Client -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">👤 Informations Client</h3>

                            @if($request->client)
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom complet</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $request->client->first_name ?? $request->client->nom ?? '' }}
                                            {{ $request->client->middle_name ?? '' }}
                                            {{ $request->client->last_name ?? $request->client->prenom ?? '' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Code Client</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->client->client_id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->client->phone ?? $request->client->telephone ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->client->email ?? 'N/A' }}</dd>
                                    </div>
                                </dl>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">Aucune information client disponible</p>
                            @endif
                        </div>
                    </div>

                    <!-- Informations Compte -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">🏦 Informations Compte</h3>

                            @if($request->account)
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Numéro de compte</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $request->account->account_id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Solde actuel</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ number_format($request->account->amount_after ?? $request->account->balance ?? 0, 2) }} GDS
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Statut du compte</dt>
                                        <dd class="mt-1">
                                            @php
                                                $status = $request->account->status;
                                                $isActive = in_array($status, ['actif', 'active']);
                                                $isClosed = in_array($status, ['clos', 'cloture', 'closed']);
                                                $badgeClass = $isActive
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                    : ($isClosed
                                                        ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300');
                                                $statusLabel = match($status) {
                                                    'actif', 'active' => 'Actif',
                                                    'inactif', 'inactive' => 'Inactif',
                                                    'clos', 'cloture', 'closed' => 'Clôturé',
                                                    'pending' => 'En attente',
                                                    default => ucfirst($status)
                                                };
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>

                                @if($request->balance_before && $request->balance_after)
                                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Aperçu de la transaction:</p>
                                        <div class="grid grid-cols-3 gap-2 text-sm">
                                            <div>
                                                <p class="text-blue-700 dark:text-blue-400">Solde avant</p>
                                                <p class="font-semibold text-blue-900 dark:text-blue-300">{{ number_format($request->balance_before, 2) }} GDS</p>
                                            </div>
                                            <div>
                                                <p class="text-blue-700 dark:text-blue-400">Montant</p>
                                                <p class="font-semibold text-red-600">-{{ number_format($request->amount, 2) }} GDS</p>
                                            </div>
                                            <div>
                                                <p class="text-blue-700 dark:text-blue-400">Solde après</p>
                                                <p class="font-semibold text-blue-900 dark:text-blue-300">{{ number_format($request->balance_after, 2) }} GDS</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($request->account && in_array($request->account->status, ['clos', 'cloture', 'closed']))
                                    <div class="mt-4 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <div>
                                                <h4 class="text-sm font-semibold text-orange-900 dark:text-orange-300">Compte clôturé</h4>
                                                <p class="text-sm text-orange-800 dark:text-orange-400 mt-1">Ce compte est clôturé. Les opérations de retrait ne sont pas recommandées.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 dark:text-gray-400">Aucune information de compte disponible</p>
                            @endif
                        </div>
                    </div>

                    <!-- Détails de retrait -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">💳 Détails de Retrait</h3>

                            @if($request->method == 'wallet')
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Numéro de téléphone (Portefeuille)</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $request->wallet_phone }}</dd>
                                    </div>
                                </dl>
                            @elseif($request->method == 'bank')
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom de la banque</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->bank_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Numéro de compte bancaire</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $request->bank_account_number }}</dd>
                                    </div>
                                    <div class="md:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Titulaire du compte</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->bank_account_holder }}</dd>
                                    </div>
                                </dl>
                            @endif
                        </div>
                    </div>

                    <!-- Note admin -->
                    @if($request->admin_note)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-300 mb-2">📝 Note de l'administrateur</h3>
                                <p class="text-sm text-yellow-800 dark:text-yellow-400">{{ $request->admin_note }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions et historique -->
                <div class="space-y-6">
                    <!-- Actions -->
                    @php
                        $accountIsClosed = $request->account && in_array($request->account->status, ['clos', 'cloture', 'closed']);
                    @endphp

                    @if($request->canBeProcessed() && !$accountIsClosed)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">⚙️ Actions</h3>

                                <!-- Approuver -->
                                <form action="{{ route('admin.withdrawals.approve', $request->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir approuver cette demande? Le montant sera déduit du compte.');">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="approve_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Note (optionnelle)</label>
                                        <textarea name="admin_note" id="approve_note" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="Remarques..."></textarea>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        ✓ Approuver et Traiter
                                    </button>
                                </form>

                                <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>

                                <!-- Mettre en traitement -->
                                <form action="{{ route('admin.withdrawals.update-status', $request->id) }}" method="POST" onsubmit="return confirm('Mettre cette demande en traitement?');">
                                    @csrf
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        🔄 Mettre en Traitement
                                    </button>
                                </form>

                                <div class="my-4 border-t border-gray-200 dark:border-gray-700"></div>

                                <!-- Rejeter -->
                                <form action="{{ route('admin.withdrawals.reject', $request->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter cette demande?');">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="reject_note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Raison du rejet *</label>
                                        <textarea name="admin_note" id="reject_note" rows="3" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="Indiquez la raison du rejet..."></textarea>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        ✗ Rejeter la Demande
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Historique de traitement -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">📋 Historique</h3>

                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 dark:text-blue-400 text-xs">📝</span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Demande créée</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $request->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>

                                @if($request->processed_at)
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 {{ $request->status == 'completed' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-full flex items-center justify-center">
                                                <span class="{{ $request->status == 'completed' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-xs">
                                                    {{ $request->status == 'completed' ? '✓' : '✗' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $request->getStatusLabel() }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $request->processed_at->format('d/m/Y à H:i') }}</p>
                                            @if($request->processed_by)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Par: Utilisateur #{{ $request->processed_by }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($request->transaction_id)
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                                <span class="text-purple-600 dark:text-purple-400 text-xs">💰</span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Transaction créée</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">ID: #{{ $request->transaction_id }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
