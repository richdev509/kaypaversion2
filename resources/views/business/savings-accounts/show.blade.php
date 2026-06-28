<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('business.entities.show', $account->business) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight font-mono">
                        {{ $account->account_number }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $account->business->name }} — Compte Épargne KEB</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
            @endif

            {{-- Alerte si business non opérationnel --}}
            @if(!$account->business->isOperational())
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="text-sm text-yellow-700 dark:text-yellow-400">
                    <p class="font-medium">Opérations de dépôt et retrait désactivées</p>
                    <p class="mt-0.5">
                        @if($account->business->status_kyc !== 'verified')
                            Le KYC de ce business n'a pas encore été approuvé par l'administration.
                        @else
                            Ce business est suspendu ou clôturé.
                        @endif
                    </p>
                </div>
            </div>
            @endif

            <!-- Solde -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Solde</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                        {{ number_format($account->balance, 2) }}
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">HTG</span>
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Statut compte</p>
                    <p class="text-lg font-semibold mt-2 {{ $account->isActive() ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $account->getStatusLabel() }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dernier intérêt</p>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">
                        {{ $account->last_interest_at?->format('d/m/Y') ?? '—' }}
                    </p>
                </div>
            </div>

            <!-- Actions dépôt / retrait -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Opérations</h3>
                <div class="flex gap-3">
                    @if($account->business->isOperational() && $account->isActive())
                        <a href="{{ route('business.keb.deposit.form', $account) }}"
                           class="flex-1 text-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            Dépôt
                        </a>
                        <a href="{{ route('business.keb.withdraw.form', $account) }}"
                           class="flex-1 text-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                            Retrait
                        </a>
                    @else
                        <button disabled class="flex-1 px-4 py-3 bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                            Dépôt — indisponible
                        </button>
                        <button disabled class="flex-1 px-4 py-3 bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                            Retrait — indisponible
                        </button>
                    @endif
                </div>
                @if(!$account->business->isOperational())
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                    {{ $account->business->status_kyc !== 'verified' ? 'KYC business en attente d\'approbation.' : 'Business suspendu.' }}
                </p>
                @endif
            </div>

            <!-- Informations du compte -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Informations du compte</h3>
                <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">N° Compte</dt>
                        <dd class="font-mono font-semibold text-gray-900 dark:text-gray-100 mt-0.5">{{ $account->account_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Business</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">
                            <a href="{{ route('business.entities.show', $account->business) }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $account->business->name }}
                            </a>
                            <span class="text-xs font-mono text-gray-400 ml-1">({{ $account->business->business_number }})</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">KYC Business</dt>
                        <dd class="mt-0.5">
                            @if($account->business->status_kyc === 'verified')
                                <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Vérifié</span>
                            @else
                                <span class="px-2 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full">{{ $account->business->getStatusKycLabel() }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Ouvert le</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $account->created_at?->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Créé par</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $account->creator?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Dernier intérêt appliqué</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">
                            {{ $account->last_interest_at?->format('d/m/Y H:i') ?? 'Jamais' }}
                        </dd>
                    </div>
                </dl>
            </div>

        </div>
    </div>
</x-app-layout>
