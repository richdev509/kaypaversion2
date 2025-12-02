<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Détails du Compte
            </h2>
            <a href="{{ route('clients.show', $account->client) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour au client
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Informations principales -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $account->account_id ?? 'N/A' }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Client: <a href="{{ route('clients.show', $account->client) }}" class="text-blue-600 hover:underline">{{ $account->client->full_name }}</a>
                            </p>
                        </div>
                        <div class="flex gap-2 items-center">
                            @if($account->status === 'actif')
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    ✅ Actif
                                </span>
                            @elseif($account->status === 'inactif')
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    ⏸️ Inactif
                                </span>
                            @elseif($account->status === 'pending')
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    ⏳ En attente
                                </span>
                            @elseif($account->status === 'cloture')
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    🔒 Clôturé
                                </span>
                            @elseif($account->status === 'clos')
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    ✗ Clos
                                </span>
                            @else
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    {{ ucfirst($account->status) }}
                                </span>
                            @endif

                            @if($account->hasDebt())
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    ⚠ Dette active
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Changement de statut (Admin et Comptable uniquement) -->
                    @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            ⚙️ Gestion du Statut
                        </h4>
                        <form action="{{ route('accounts.status.update', $account) }}" method="POST" class="flex flex-wrap gap-3 items-end">
                            @csrf
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    Nouveau statut
                                </label>
                                <select name="status" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                    <option value="actif" {{ $account->status === 'actif' ? 'selected' : '' }}>✅ Actif</option>
                                    <option value="inactif" {{ $account->status === 'inactif' ? 'selected' : '' }}>⏸️ Inactif</option>
                                    <option value="pending" {{ $account->status === 'pending' ? 'selected' : '' }}>⏳ En attente</option>
                                    <option value="cloture" {{ $account->status === 'cloture' ? 'selected' : '' }}>🔒 Clôturé</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[300px]">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                    Raison du changement (optionnel)
                                </label>
                                <input type="text" name="reason" maxlength="500"
                                       placeholder="Ex: Compte inactif depuis 6 mois"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                            </div>
                            <button type="submit"
                                    onclick="return confirm('Êtes-vous sûr de vouloir changer le statut de ce compte ?')"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                💾 Mettre à jour
                            </button>
                        </form>
                    </div>
                    @endif

                    <!-- Solde et statistiques -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Solde actuel</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format($account->solde_virtuel, 2) }} HTG
                            </p>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Dispo pour retrait</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ number_format($account->getAvailableForWithdrawal(), 2) }} HTG
                            </p>
                        </div>

                        @if($account->hasDebt())
                            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Dette</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    {{ number_format($account->withdraw, 2) }} HTG
                                </p>
                            </div>
                        @endif

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Montant journalier</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ number_format($account->montant_journalier, 2) }} HTG
                            </p>
                        </div>
                    </div>

                    <!-- Informations du compte -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Plan d'épargne</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Nom:</span> {{ $account->plan->name ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Durée:</span> {{ $account->plan->duree ?? 'N/A' }} jours
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Total prévu:</span> {{ number_format(($account->plan->duree ?? 0) * $account->montant_journalier, 2) }} HTG
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Dates</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Début:</span> {{ $account->date_debut->format('d/m/Y') }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Fin:</span> {{ $account->date_fin->format('d/m/Y') }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Jours restants:</span>
                                    @if($account->isPlanCompleted())
                                        <span class="text-green-600">Terminé</span>
                                    @else
                                        {{ $account->getDaysRemaining() }} jours
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Actions rapides</h4>
                            <div class="flex flex-col gap-2">
                                @if($account->status === 'actif')
                                    <button onclick="openDepotModal()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg text-center transition">
                                        💰 Effectuer dépôt
                                    </button>
                                    @if($account->solde_virtuel > 0 && !$account->hasDebt())
                                        <a href="{{ route('withdrawals.create', $account) }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg text-center transition">
                                            💸 Effectuer retrait
                                        </a>
                                    @endif
                                @endif

                                @if(Auth::user()->hasPermissionTo('accounts.edit') || Auth::user()->isAdmin())
                                    <a href="{{ route('accounts.corrections', $account) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg text-center transition">
                                        🔧 Corriger une transaction
                                    </a>
                                @endif

                                <a href="#" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg text-center transition">
                                    📊 Voir rapport
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions récentes -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Transactions récentes
                    </h3>

                    @if($account->transactions->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucune transaction</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Les transactions apparaîtront ici.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Solde après</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Méthode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Note</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($account->transactions as $transaction)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($transaction->type === 'PAIEMENT')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Dépôt
                                                    </span>
                                                @elseif($transaction->type === 'RETRAIT')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        Retrait
                                                    </span>
                                                @elseif($transaction->type === 'AJUSTEMENT')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Ajustement
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ $transaction->type }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} HTG
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($transaction->amount_after, 2) }} HTG
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ strtoupper($transaction->method ?? 'N/A') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $transaction->note ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                Voir toutes les transactions →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Dépôt -->
    <div id="depotModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-2xl transform transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b dark:border-gray-700 bg-gradient-to-r from-green-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 rounded-t-xl">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span class="text-3xl">💰</span>
                    Effectuer un dépôt
                </h3>
                <button onclick="closeDepotModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                <!-- Informations du compte -->
                <div class="p-5 bg-blue-600 dark:bg-blue-700 rounded-lg shadow-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-blue-100 dark:text-blue-200 text-xs font-medium mb-1">Compte:</p>
                        <p class="font-bold text-white text-lg">{{ $account->account_id }}</p>
                    </div>
                    <div>
                        <p class="text-blue-100 dark:text-blue-200 text-xs font-medium mb-1">Client:</p>
                        <p class="font-bold text-white text-lg">{{ $account->client->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-blue-100 dark:text-blue-200 text-xs font-medium mb-1">Solde actuel:</p>
                        <p class="font-bold text-white text-xl">{{ number_format($account->solde_virtuel, 2) }} HTG</p>
                    </div>
                    <div>
                        <p class="text-blue-100 dark:text-blue-200 text-xs font-medium mb-1">Montant journalier:</p>
                        <p class="font-bold text-white text-xl">{{ number_format($account->montant_journalier, 2) }} HTG</p>
                    </div>
                </div>
                @if($account->hasDebt())
                    <div class="mt-3 p-3 bg-orange-50 dark:bg-orange-900/20 rounded border border-orange-200 dark:border-orange-800">
                        <p class="text-sm font-medium text-orange-800 dark:text-orange-200">
                            ⚠ Dette active: {{ number_format($account->withdraw, 2) }} HTG
                        </p>
                        <p class="text-xs text-orange-600 dark:text-orange-300 mt-1">
                            Le dépôt sera d'abord utilisé pour rembourser cette dette.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Formulaire -->
            <form action="{{ route('payments.store', $account) }}" method="POST" class="p-6 pt-0">
                @csrf

                <!-- Montant -->
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Montant <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            step="0.01"
                            min="1"
                            max="{{ ($account->plan->duree * $account->montant_journalier) - $account->solde_virtuel }}"
                            value="{{ old('amount', $account->montant_journalier) }}"
                            required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 pr-16"
                            oninput="calculateDays()"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm">HTG</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Montant libre (minimum 1 HTG)
                    </p>
                    <p id="days-info" class="mt-1 text-sm font-medium text-blue-600 dark:text-blue-400"></p>
                </div>

                <!-- Méthode de paiement -->
                <div class="mb-4">
                    <label for="method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Méthode de paiement <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="method"
                        name="method"
                        required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">-- Sélectionner --</option>
                        <option value="cash" {{ old('method') == 'cash' ? 'selected' : '' }}>💵 Espèces (Cash)</option>
                        <option value="moncash" {{ old('method') == 'moncash' ? 'selected' : '' }}>📱 MonCash</option>
                        <option value="bank_transfer" {{ old('method') == 'bank_transfer' ? 'selected' : '' }}>🏦 Virement Bancaire</option>
                    </select>
                </div>

                <!-- Référence -->
                <div class="mb-4">
                    <label for="reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Référence (optionnel)
                    </label>
                    <input
                        type="text"
                        id="reference"
                        name="reference"
                        value="{{ old('reference') }}"
                        placeholder="Ex: REF-12345, Transaction MonCash..."
                        maxlength="100"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Numéro de transaction, reçu, etc.
                    </p>
                </div>

                <!-- Note -->
                <div class="mb-6">
                    <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Note (optionnel)
                    </label>
                    <textarea
                        id="note"
                        name="note"
                        rows="2"
                        maxlength="500"
                        placeholder="Ajouter une note..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >{{ old('note') }}</textarea>
                </div>

                <!-- Boutons -->
                <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t dark:border-gray-700">
                    <button
                        type="button"
                        onclick="closeDepotModal()"
                        class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition-all"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        class="px-8 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center gap-2"
                    >
                        <span>✅</span>
                        Confirmer le dépôt
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const montantJournalier = {{ $account->montant_journalier }};

        function openDepotModal() {
            document.getElementById('depotModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => calculateDays(), 100);
        }

        function closeDepotModal() {
            document.getElementById('depotModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function calculateDays() {
            const amountInput = document.getElementById('amount');
            if (!amountInput) return;

            const amount = parseFloat(amountInput.value) || 0;
            const days = (amount / montantJournalier).toFixed(2);
            const daysInfo = document.getElementById('days-info');

            if (daysInfo && amount > 0) {
                daysInfo.textContent = `≈ ${days} jour(s) de cotisation`;
                daysInfo.classList.remove('text-red-600');
                daysInfo.classList.add('text-blue-600', 'dark:text-blue-400');
            } else if (daysInfo) {
                daysInfo.textContent = '';
            }
        }

        // Attendre que le DOM soit chargé
        document.addEventListener('DOMContentLoaded', function() {
            // Fermer le modal en cliquant en dehors
            const depotModal = document.getElementById('depotModal');

            if (depotModal) {
                depotModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDepotModal();
                    }
                });
            }

            // Fermer avec la touche Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDepotModal();
                }
            });
        });
    </script>
</x-app-layout>
