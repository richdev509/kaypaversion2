<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profil de {{ $user->name }}
            </h2>
            <div class="space-x-2">
                @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Modifier
                </a>
                @endcan
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('users.permissions.edit', $user) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    🔐 Permissions
                </a>
                @endif
                <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Retour
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Informations de base -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center space-x-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        </div>

                        <!-- Infos -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $user->name }}</h3>
                            <div class="space-y-2">
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $user->email }}
                                </div>
                                @if($user->telephone)
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $user->telephone }}
                                </div>
                                @endif
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $user->role_badge_color }}">
                                        {{ $user->role_name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations supplémentaires -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations supplémentaires</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Branche</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($user->branch)
                                        <a href="{{ route('branches.show', $user->branch) }}" class="text-blue-600 hover:underline">
                                            {{ $user->branch->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">Non assigné</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dernière modification</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y à H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiques</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Transactions créées</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($stats['total_transactions']) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dépôts créés</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($stats['total_deposits']) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Retraits créés</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($stats['total_withdrawals']) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Montant total des dépôts</dt>
                                <dd class="mt-1 text-2xl font-semibold text-green-600">{{ number_format($stats['total_amount_deposits'], 2) }} HTG</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Montant total des retraits</dt>
                                <dd class="mt-1 text-2xl font-semibold text-red-600">{{ number_format($stats['total_amount_withdrawals'], 2) }} HTG</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissions du rôle</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($user->getUserPermissions() as $permission)
                        <div class="flex items-center space-x-2 text-sm">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">{{ $permission }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Activité récente -->
            @if($user->createdTransactions->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activité récente</h3>

                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Dernières transactions</h4>
                        <div class="space-y-2">
                            @foreach($user->createdTransactions->take(10) as $transaction)
                            <div class="flex justify-between items-center text-sm border-b border-gray-200 pb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-700">Transaction #{{ $transaction->id }}</span>
                                    @if($transaction->type === 'PAIEMENT')
                                        <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Dépôt</span>
                                    @elseif($transaction->type === 'RETRAIT')
                                        <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-800">Retrait</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800">{{ $transaction->type }}</span>
                                    @endif
                                    <span class="text-gray-600">{{ number_format($transaction->amount, 2) }} HTG</span>
                                </div>
                                <span class="text-gray-500">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
