<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('current-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    ← Retour
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Compte Courant — {{ $currentAccount->account_number }}
                </h2>
            </div>
            <div class="flex gap-2">
                @if($currentAccount->isActive())
                <a href="{{ route('current-accounts.deposit.form', $currentAccount) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                    + Dépôt
                </a>
                <a href="{{ route('current-accounts.withdraw.form', $currentAccount) }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition">
                    - Retrait
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Info compte -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Informations du compte</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">N° Compte</dt>
                            <dd class="mt-1 font-mono font-semibold text-gray-900 dark:text-gray-100">{{ $currentAccount->account_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Statut</dt>
                            <dd class="mt-1">
                                @php
                                    $badge = match($currentAccount->status) {
                                        'actif'    => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'suspendu' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'cloture'  => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        default    => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                    {{ ucfirst($currentAccount->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Client</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $currentAccount->client?->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Téléphone</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $currentAccount->client?->phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date d'ouverture</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $currentAccount->created_at->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dernier frais service</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $currentAccount->last_fee_charged_at?->format('d/m/Y') ?? 'Aucun' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Succursale</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $currentAccount->branch?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Créé par</dt>
                            <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $currentAccount->creator?->name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Solde + actions statut -->
                <div class="space-y-4">
                    <div class="bg-blue-600 dark:bg-blue-700 shadow-sm sm:rounded-lg p-6 text-white">
                        <p class="text-sm font-medium text-blue-100">Solde actuel</p>
                        <p class="text-4xl font-bold mt-2">{{ number_format($currentAccount->balance, 2) }}</p>
                        <p class="text-sm text-blue-100 mt-1">HTG</p>
                    </div>

                    @if($canChangeStatus)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Changer le statut</p>
                        <form method="POST" action="{{ route('current-accounts.status', $currentAccount) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="w-full mb-3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm shadow-sm">
                                <option value="actif" @selected($currentAccount->status == 'actif')>Actif</option>
                                <option value="suspendu" @selected($currentAccount->status == 'suspendu')>Suspendu</option>
                                <option value="cloture" @selected($currentAccount->status == 'cloture')>Clôturé</option>
                            </select>
                            <button type="submit" class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm">
                                Mettre à jour
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historique transactions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Historique des transactions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Solde après</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Méthode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Opérateur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Note</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($transactions as $tx)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $tx->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeBadge = match($tx->type) {
                                            'DEPOT'           => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'RETRAIT'         => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'FRAIS_OUVERTURE' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'FRAIS_SERVICE'   => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            default           => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeBadge }}">
                                        {{ $tx->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $tx->isCredit() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $tx->isCredit() ? '+' : '-' }}{{ number_format($tx->amount, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($tx->balance_after, 2) }} HTG
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $tx->method ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    @if($tx->creator)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs flex items-center justify-center font-bold">{{ strtoupper(substr($tx->creator->name, 0, 1)) }}</span>
                                            {{ $tx->creator->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-xs">Système</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $tx->note ?? '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Aucune transaction enregistrée.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
