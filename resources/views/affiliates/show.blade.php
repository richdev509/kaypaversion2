<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Détails Affilié : {{ $affiliate->nom_complet }}
            </h2>
            <a href="{{ route('affiliates.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                ← Retour à la liste
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Informations Affilié -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informations Générales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nom Complet</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $affiliate->nom_complet }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                                {{ $affiliate->email }}
                                @if($affiliate->email_verifie)
                                    <span class="text-green-600 text-sm">✓ Vérifié</span>
                                @else
                                    <span class="text-red-600 text-sm">✗ Non vérifié</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $affiliate->telephone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">WhatsApp</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $affiliate->whatsapp ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Code de Parrainage</label>
                            <p class="mt-1 text-2xl font-bold text-purple-600">{{ $affiliate->code_parrain ?? 'Pas encore généré' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Statut</label>
                            <p class="mt-1">
                                @if($affiliate->status === 'en_attente')
                                    <span class="px-3 py-1 text-sm font-semibold rounded bg-yellow-100 text-yellow-800">En Attente</span>
                                @elseif($affiliate->status === 'approuve')
                                    <span class="px-3 py-1 text-sm font-semibold rounded bg-green-100 text-green-800">Approuvé</span>
                                @elseif($affiliate->status === 'rejete')
                                    <span class="px-3 py-1 text-sm font-semibold rounded bg-red-100 text-red-800">Rejeté</span>
                                @else
                                    <span class="px-3 py-1 text-sm font-semibold rounded bg-gray-100 text-gray-800">Bloqué</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Solde Bonus</label>
                            <p class="mt-1 text-2xl font-bold text-green-600">{{ $affiliate->solde_formatte }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Date d'inscription</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $affiliate->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Parrainages</p>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $stats['total_parrainages'] }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Parrainages Validés</p>
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">{{ $stats['parrainages_valides'] }}</p>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Gagné</p>
                                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">{{ number_format($stats['total_gagne'], 0) }} GDS</p>
                            </div>
                            <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Payé</p>
                                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-2">{{ number_format($stats['total_paye'], 0) }} GDS</p>
                            </div>
                            <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                                <svg class="w-8 h-8 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Actions</h3>
                    <div class="flex gap-4 flex-wrap">
                        @if($affiliate->status === 'en_attente' && $affiliate->email_verifie)
                            <form method="POST" action="{{ route('affiliates.approve', $affiliate) }}" onsubmit="return confirm('Approuver cet affilié ? Un code de parrainage sera généré et envoyé par email.')">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Approuver
                                </button>
                            </form>

                            <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">
                                Rejeter
                            </button>
                        @endif

                        @if($affiliate->status === 'approuve' && $affiliate->code_parrain)
                            <form method="POST" action="{{ route('affiliates.resend-code', $affiliate) }}">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                                    Renvoyer le Code
                                </button>
                            </form>

                            <form method="POST" action="{{ route('affiliates.toggle-block', $affiliate) }}" onsubmit="return confirm('Bloquer cet affilié ?')">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg">
                                    Bloquer
                                </button>
                            </form>

                            @if($affiliate->solde_bonus > 0)
                                <button onclick="document.getElementById('paiement-modal').classList.remove('hidden')" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg">
                                    Effectuer un Paiement
                                </button>
                            @endif
                        @endif

                        @if($affiliate->status === 'bloque')
                            <form method="POST" action="{{ route('affiliates.toggle-block', $affiliate) }}" onsubmit="return confirm('Débloquer cet affilié ?')">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                                    Débloquer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Liste des Parrainages -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Clients Parrainés</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Client ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nom Complet</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Téléphone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Comptes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Bonus</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date Inscription</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($affiliate->clients as $client)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $client->client_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $client->first_name }} {{ $client->last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $client->phone }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $client->accounts->count() }} compte(s)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                        25.00 GDS
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $client->created_at->format('d/m/Y') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucun client parrainé</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Historique Paiements -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Historique des Paiements</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Méthode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Effectué par</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Note</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($affiliate->paiements as $paiement)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $paiement->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                        {{ $paiement->montant_formatte }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst(str_replace('_', ' ', $paiement->methode)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $paiement->effectuePar->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $paiement->note ?? '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucun paiement</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rejet -->
    <div id="reject-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Rejeter la Demande</h3>
            <form method="POST" action="{{ route('affiliates.reject', $affiliate) }}">
                @csrf
                <textarea name="motif_rejet" rows="4" class="w-full border rounded p-2 mb-4" placeholder="Motif du rejet..." required></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Rejeter</button>
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Paiement -->
    <div id="paiement-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Effectuer un Paiement</h3>
            <form method="POST" action="{{ route('affiliates.paiement', $affiliate) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Montant (Max: {{ $affiliate->solde_bonus }} GDS)</label>
                    <input type="number" name="montant" step="0.01" max="{{ $affiliate->solde_bonus }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Méthode</label>
                    <select name="methode" class="w-full border rounded p-2" required>
                        <option value="cash">Cash</option>
                        <option value="moncash">MonCash</option>
                        <option value="bank_transfer">Virement Bancaire</option>
                        <option value="compte_kaypa">Compte Kaypa</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Note (optionnel)</label>
                    <textarea name="note" rows="2" class="w-full border rounded p-2"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded">Payer</button>
                    <button type="button" onclick="document.getElementById('paiement-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
