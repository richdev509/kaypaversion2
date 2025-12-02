<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Détails Client
            </h2>
            <a href="{{ route('clients.search') }}?search={{ request('search', '') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour à la recherche
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Informations personnelles -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-center">
                            <div class="h-20 w-20 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold">
                                {{ strtoupper(substr($client->first_name ?? 'N', 0, 1)) }}{{ strtoupper(substr($client->last_name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="ml-6">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $client->full_name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    ID Client: <span class="font-mono">{{ $client->client_id ?? 'N/A' }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @if($client->status_kyc === 'verified')
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    ✓ KYC Vérifié
                                </span>
                            @elseif($client->status_kyc === 'pending')
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    ⏳ KYC En attente
                                </span>
                            @else
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    ✗ Non vérifié
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Contact -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Contact</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Téléphone:</span> {{ $client->phone ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Email:</span> {{ $client->email ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Adresse:</span> {{ $client->address ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Informations personnelles -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Informations</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Sexe:</span> {{ $client->sexe ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Date naissance:</span> {{ $client->date_naissance?->format('d/m/Y') ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Nationalité:</span> {{ $client->nationalite ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Documents</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Type:</span> {{ $client->document_id_type ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Numéro:</span> {{ $client->document_id_number ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">NIF/CIN:</span> {{ $client->id_nif_cin ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Carte KAYPA -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Carte KAYPA</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Numéro:</span>
                                    <span class="font-mono">{{ $client->numero_carte ?? $client->card_number ?? 'N/A' }}</span>
                                </p>
                                @if($client->date_emission)
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-medium">Émission:</span> {{ $client->date_emission->format('d/m/Y') }}
                                    </p>
                                @endif
                                @if($client->date_expiration)
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-medium">Expiration:</span> {{ $client->date_expiration->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Dates -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Dates système</h4>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Créé le:</span> {{ $client->created_at->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">Modifié le:</span> {{ $client->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Actions</h4>
                            <div class="flex flex-col gap-2">
                                @if($client->front_id_path || $client->selfie_path)
                                    <button onclick="openIdentityModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg text-center transition">
                                        🪪 Vérifier l'identité
                                    </button>
                                @endif
                                <a href="{{ route('clients.edit', $client) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg text-center transition">
                                    Modifier
                                </a>
                                @if($client->status_kyc !== 'verified')
                                    <a href="{{ route('clients.verify-kyc', $client) }}" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg text-center transition">
                                        🔍 Vérifier KYC
                                    </a>
                                @endif
                                <a href="{{ route('clients.update-kyc-form', $client) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg text-center transition">
                                    📸 Mise à jour KYC
                                </a>
                                <a href="{{ route('accounts.create') }}?client_id={{ $client->id }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg text-center transition">
                                    + Nouveau Compte
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comptes du client -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Comptes d'épargne ({{ $client->accounts->count() }})
                        </h3>
                    </div>

                    @if($client->accounts->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucun compte</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ce client n'a pas encore de compte d'épargne.</p>
                            <div class="mt-6">
                                <a href="{{ route('accounts.create') }}?client_id={{ $client->id }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Créer un compte
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Compte ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Solde</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Dates</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($client->accounts as $account)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-mono text-gray-900 dark:text-gray-100">
                                                    {{ $account->account_id ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $account->plan->name ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $account->montant_journalier }} HTG/jour</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ number_format($account->solde_virtuel, 2) }} HTG
                                                </div>
                                                @if($account->hasDebt())
                                                    <div class="text-xs text-red-600 dark:text-red-400">
                                                        Dette: {{ number_format($account->withdraw, 2) }} HTG
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($account->status === 'actif')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Actif
                                                    </span>
                                                @elseif($account->status === 'clos')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Clos
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Suspendu
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <div>Début: {{ $account->date_debut->format('d/m/Y') }}</div>
                                                <div>Fin: {{ $account->date_fin->format('d/m/Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('accounts.show', $account) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                                    Détails
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de vérification d'identité -->
    <div id="identityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto relative">
            <!-- Bouton fermeture -->
            <button onclick="closeIdentityModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl font-bold z-10">
                ×
            </button>

            <!-- En-tête -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">🪪 Vérification d'identité</h3>
                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    <p><strong>Client:</strong> {{ $client->prenom }} {{ $client->nom }}</p>
                    <p><strong>Type de document:</strong>
                        @if($client->document_id_type === 'ID')
                            Carte d'identité
                        @elseif($client->document_id_type === 'Permis')
                            Permis de conduire
                        @elseif($client->document_id_type === 'Passeport')
                            Passeport
                        @endif
                    </p>
                    <p><strong>Numéro:</strong>
                        @if($client->document_id_type === 'ID')
                            {{ $client->numero_carte ?? 'N/A' }}
                        @elseif($client->document_id_type === 'Permis')
                            {{ $client->permis_number ?? 'N/A' }}
                        @elseif($client->document_id_type === 'Passeport')
                            {{ $client->passport_number ?? 'N/A' }}
                        @endif
                    </p>
                    <p><strong>Statut KYC:</strong>
                        <span class="px-2 py-1 rounded-full text-xs
                            @if($client->status_kyc === 'verified') bg-green-100 text-green-800
                            @elseif($client->status_kyc === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($client->status_kyc) }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Contenu: Photos -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Photo Selfie -->
                    <div class="text-center">
                        <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">📸 Photo Selfie</h4>
                        @if($client->selfie_path)
                            <img src="{{ asset('storage/' . $client->selfie_path) }}"
                                 alt="Selfie"
                                 class="w-full h-64 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer hover:opacity-90 transition"
                                 onclick="openImageFullscreen(this.src)">
                        @else
                            <div class="w-full h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <span class="text-gray-500 dark:text-gray-400">Photo non disponible</span>
                            </div>
                        @endif
                    </div>

                    <!-- Photo Recto -->
                    <div class="text-center">
                        <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">🪪 Recto du document</h4>
                        @if($client->front_id_path)
                            <img src="{{ asset('storage/' . $client->front_id_path) }}"
                                 alt="Recto"
                                 class="w-full h-64 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer hover:opacity-90 transition"
                                 onclick="openImageFullscreen(this.src)">
                        @else
                            <div class="w-full h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <span class="text-gray-500 dark:text-gray-400">Photo non disponible</span>
                            </div>
                        @endif
                    </div>

                    <!-- Photo Verso -->
                    <div class="text-center">
                        <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">🔄 Verso du document</h4>
                        @if($client->back_id_path)
                            <img src="{{ asset('storage/' . $client->back_id_path) }}"
                                 alt="Verso"
                                 class="w-full h-64 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer hover:opacity-90 transition"
                                 onclick="openImageFullscreen(this.src)">
                        @else
                            <div class="w-full h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <span class="text-gray-500 dark:text-gray-400">Photo non disponible</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pied de page -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button onclick="closeIdentityModal()" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal plein écran pour images -->
    <div id="fullscreenModal" class="fixed inset-0 bg-black bg-opacity-90 hidden z-[60] flex items-center justify-center p-4" onclick="closeFullscreen()">
        <button onclick="closeFullscreen()" class="absolute top-4 right-4 text-white text-4xl font-bold hover:text-gray-300">
            ×
        </button>
        <img id="fullscreenImage" src="" alt="Image agrandie" class="max-w-full max-h-full object-contain">
    </div>

    <script>
        function openIdentityModal() {
            document.getElementById('identityModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeIdentityModal() {
            document.getElementById('identityModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openImageFullscreen(imageSrc) {
            document.getElementById('fullscreenImage').src = imageSrc;
            document.getElementById('fullscreenModal').classList.remove('hidden');
        }

        function closeFullscreen() {
            document.getElementById('fullscreenModal').classList.add('hidden');
            document.getElementById('fullscreenImage').src = '';
        }

        // Fermeture modal avec clic extérieur
        document.getElementById('identityModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeIdentityModal();
            }
        });

        // Fermeture avec touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeIdentityModal();
                closeFullscreen();
            }
        });
    </script>
</x-app-layout>
