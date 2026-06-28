<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('business.entities.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $entity->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $entity->business_number }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('business.entities.edit', $entity) }}"
                   class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                    Modifier
                </a>
                @if($entity->status_kyc !== 'verified')
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('comptable'))
                    <form method="POST" action="{{ route('business.entities.kyc', $entity) }}" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" onclick="return confirm('Confirmer la vérification KYC de ce business ?')"
                            class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                            Approuver KYC
                        </button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
            @endif

            <!-- Infos Business -->
            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Informations Business</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Propriétaire</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $entity->ownerClient?->first_name }} {{ $entity->ownerClient?->last_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Raison sociale</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $entity->legal_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Profil</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $entity->getProfileLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Téléphone</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $entity->phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $entity->email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Adresse</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $entity->address ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Créé le</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $entity->created_at?->format('d/m/Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Statuts & Actions -->
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-3">Statuts</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">KYC</span>
                                @if($entity->status_kyc === 'verified')
                                    <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Vérifié</span>
                                @elseif($entity->status_kyc === 'pending')
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-full">Pending</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full">{{ $entity->status_kyc }}</span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Business</span>
                                <span class="px-2 py-0.5 text-xs {{ $entity->isActive() ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }} rounded-full">
                                    {{ $entity->getStatusLabel() }}
                                </span>
                            </div>
                        </div>
                        @if($entity->kyc_verified_at)
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">KYC vérifié le {{ $entity->kyc_verified_at->format('d/m/Y') }}</p>
                        @endif
                    </div>

                    <!-- Ouvrir comptes -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Comptes</h4>
                        @if($entity->currentAccount)
                            <a href="{{ route('business.kcb.show', $entity->currentAccount) }}"
                               class="block w-full text-center px-3 py-2 text-sm bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 transition font-mono">
                                KCB: {{ $entity->currentAccount->account_number }}
                            </a>
                        @else
                            <form method="POST" action="{{ route('business.entities.kcb.open', $entity) }}">
                                @csrf
                                <button type="submit" class="w-full px-3 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                                    Ouvrir KCB
                                </button>
                            </form>
                        @endif

                        @if($entity->savingsAccount)
                            <a href="{{ route('business.keb.show', $entity->savingsAccount) }}"
                               class="block w-full text-center px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700/30 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition font-mono">
                                KEB: {{ $entity->savingsAccount->account_number }}
                            </a>
                        @else
                            <form method="POST" action="{{ route('business.entities.keb.open', $entity) }}">
                                @csrf
                                <button type="submit" class="w-full px-3 py-2 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                                    Ouvrir KEB
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Membres -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Utilisateurs Business</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Client</th>
                                <th class="px-4 py-3 text-left">Rôle</th>
                                <th class="px-4 py-3 text-left">Approbation Payroll</th>
                                <th class="px-4 py-3 text-left">Demande Crédit</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($entity->businessUsers as $bu)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    {{ $bu->client?->first_name }} {{ $bu->client?->last_name }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($bu->isOwner())
                                        <span class="px-2 py-0.5 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full">Propriétaire</span>
                                    @else
                                        <span class="text-gray-600 dark:text-gray-400">{{ $bu->getRoleLabel() }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">{{ $bu->can_approve_payroll ? '✓' : '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $bu->can_request_credit ? '✓' : '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($bu->is_active)
                                        <span class="text-green-600 dark:text-green-400 text-xs">Actif</span>
                                    @else
                                        <span class="text-gray-400 text-xs">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Aucun membre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Crédits actifs -->
            @if($entity->creditLimits->count())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Crédits en cours</h3>
                    <a href="{{ route('business.credit.index') }}?business_id={{ $entity->id }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Voir tout</a>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($entity->creditLimits as $credit)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($credit->approved_limit, 2) }} HTG
                            </span>
                            <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">— {{ $credit->ratePlan?->name ?? 'Taux manuel' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-xs {{ $credit->isPending() ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300' : 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' }} rounded-full">
                                {{ $credit->getStatusLabel() }}
                            </span>
                            <a href="{{ route('business.credit.show', $credit) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Détail</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Dernières transactions KCB -->
            @if($entity->currentAccount)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">
                        Compte KCB — Solde :
                        <span class="text-indigo-600 dark:text-indigo-400">{{ number_format($entity->currentAccount->balance, 2) }} HTG</span>
                    </h3>
                    <a href="{{ route('business.kcb.show', $entity->currentAccount) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Voir tout</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-right">Montant</th>
                                <th class="px-4 py-3 text-right">Solde après</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($entity->currentAccount->transactions as $tx)
                            <tr>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $tx->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="{{ $tx->isCredit() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                        {{ $tx->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right {{ $tx->isCredit() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                    {{ $tx->isDebit() ? '-' : '+' }}{{ number_format($tx->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ number_format($tx->balance_after, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Aucune transaction.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
