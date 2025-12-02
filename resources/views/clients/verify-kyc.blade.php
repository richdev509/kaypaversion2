<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Vérification KYC - {{ $client->full_name }}
            </h2>
            <a href="{{ route('clients.show', $client) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <h4 class="font-semibold mb-2">❌ Erreurs de validation :</h4>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Informations client -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            👤 Informations du client
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div><span class="font-medium">Nom complet:</span> {{ $client->full_name }}</div>
                            <div><span class="font-medium">Téléphone:</span> {{ $client->phone }}</div>
                            <div><span class="font-medium">Email:</span> {{ $client->email ?? 'N/A' }}</div>
                            <div><span class="font-medium">Statut KYC:</span>
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    @if($client->status_kyc === 'verified') bg-green-100 text-green-800
                                    @elseif($client->status_kyc === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($client->status_kyc === 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($client->status_kyc) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('clients.update-kyc', $client) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Champs cachés pour les chemins des photos -->
                        <input type="hidden" name="piece_id_path" id="piece_id_path">
                        <input type="hidden" name="back_path" id="back_path">
                        <input type="hidden" name="selfie_path" id="selfie_path">
                        <input type="hidden" name="profil_path" id="profil_path">
                        <input type="hidden" id="upload_token" value="{{ $uploadToken }}">

                        <!-- Section: Documents d'identité -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                🆔 Documents d'Identité
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Type de pièce -->
                                <div class="md:col-span-2">
                                    <label for="piece_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Type de pièce d'identité
                                    </label>
                                    <select
                                        id="piece_type"
                                        name="piece_type"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">-- Sélectionner --</option>
                                        <option value="ID" {{ old('piece_type', $client->document_id_type) == 'ID' ? 'selected' : '' }}>Carte d'Identité Nationale (CIN)</option>
                                        <option value="Permis" {{ old('piece_type', $client->document_id_type) == 'Permis' ? 'selected' : '' }}>Permis de conduire</option>
                                        <option value="Passeport" {{ old('piece_type', $client->document_id_type) == 'Passeport' ? 'selected' : '' }}>Passeport</option>
                                    </select>
                                </div>

                                <!-- Champs pour CIN (NIU) -->
                                <div id="nui_field" class="md:col-span-2 {{ old('piece_type', $client->document_id_type) == 'ID' ? '' : 'hidden' }}">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Carte d'Identité Nationale</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="nu_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Numéro de carte (10 caractères)
                                                </label>
                                                <input
                                                    type="text"
                                                    id="nu_number"
                                                    name="nu_number"
                                                    value="{{ old('nu_number', $client->document_id_type === 'ID' ? $client->numero_carte : '') }}"
                                                    maxlength="10"
                                                    placeholder="ABC1234567"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                >
                                            </div>
                                            <div>
                                                <label for="nui_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    NIU (10 chiffres)
                                                </label>
                                                <input
                                                    type="text"
                                                    id="nui_number"
                                                    name="nui_number"
                                                    value="{{ old('nui_number', $client->document_id_type === 'ID' ? $client->id_nif_cin : '') }}"
                                                    maxlength="10"
                                                    pattern="\d{10}"
                                                    placeholder="0123456789"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Champs pour Permis -->
                                <div id="permis_field" class="md:col-span-2 {{ old('piece_type', $client->document_id_type) == 'Permis' ? '' : 'hidden' }}">
                                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Permis de Conduire</h4>
                                        <div>
                                            <label for="permis_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Numéro de permis (format: 123-456-789-0)
                                            </label>
                                            <input
                                                type="text"
                                                id="permis_number"
                                                name="permis_number"
                                                value="{{ old('permis_number', $client->document_id_type === 'Permis' ? $client->document_id_number : '') }}"
                                                maxlength="13"
                                                placeholder="AB12345678901"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Champs pour Passeport -->
                                <div id="passport_field" class="md:col-span-2 {{ old('piece_type', $client->document_id_type) == 'Passeport' ? '' : 'hidden' }}">
                                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Passeport</h4>
                                        <div>
                                            <label for="passport_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Numéro de passeport (9 caractères)
                                            </label>
                                            <input
                                                type="text"
                                                id="passport_number"
                                                name="passport_number"
                                                value="{{ old('passport_number', $client->document_id_type === 'Passeport' ? $client->document_id_number : '') }}"
                                                maxlength="9"
                                                placeholder="AB1234567"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates de validité -->
                                <div>
                                    <label for="date_emission" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Date d'émission
                                    </label>
                                    <input
                                        type="date"
                                        id="date_emission"
                                        name="date_emission"
                                        value="{{ old('date_emission', $client->date_emission?->format('Y-m-d')) }}"
                                        max="{{ date('Y-m-d') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                </div>

                                <div>
                                    <label for="date_expiration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Date d'expiration
                                    </label>
                                    <input
                                        type="date"
                                        id="date_expiration"
                                        name="date_expiration"
                                        value="{{ old('date_expiration', $client->date_expiration?->format('Y-m-d')) }}"
                                        min="{{ date('Y-m-d') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Section: Scan QR Code -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                📸 Photos de la Pièce d'Identité et Selfie
                            </h3>

                            <!-- Photos actuelles -->
                            @if($client->front_id_path || $client->back_id_path || $client->selfie_path)
                            <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">📷 Photos actuelles :</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @if($client->front_id_path)
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pièce (Avant)</p>
                                        <img src="{{ asset('storage/' . $client->front_id_path) }}" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                    </div>
                                    @endif
                                    @if($client->back_id_path)
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pièce (Arrière)</p>
                                        <img src="{{ asset('storage/' . $client->back_id_path) }}" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                    </div>
                                    @endif
                                    @if($client->selfie_path)
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Selfie</p>
                                        <img src="{{ asset('storage/' . $client->selfie_path) }}" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div id="piece-id-section">
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 rounded-lg border-2 border-blue-200 dark:border-blue-800">
                                    <div class="flex flex-col md:flex-row items-center gap-6">
                                        <div class="flex-shrink-0">
                                            {!! QrCode::size(150)->generate(route('clients.scan', ['token' => $uploadToken])) !!}
                                        </div>
                                        <div class="flex-1 text-center md:text-left">
                                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                                                📱 Scannez ce QR Code pour {{ $client->front_id_path ? 'mettre à jour' : 'ajouter' }} les photos
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                                Vous serez invité à prendre 3 photos :
                                            </p>
                                            <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                                <li>✓ Photo AVANT de la pièce d'identité</li>
                                                <li>✓ Photo ARRIÈRE de la pièce d'identité</li>
                                                <li>✓ Selfie du client</li>
                                            </ul>
                                            <div class="mt-4 flex items-center justify-center md:justify-start gap-2">
                                                <div class="animate-pulse">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">En attente de scan...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statut upload -->
                            <div id="upload-status" class="hidden mt-4">
                                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-sm font-medium text-green-800 dark:text-green-200">Nouvelles photos reçues avec succès!</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Prévisualisations -->
                            <div id="preview-section" class="hidden mt-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Aperçu des nouvelles photos :</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pièce (Avant)</p>
                                        <img id="preview_front" src="" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pièce (Arrière)</p>
                                        <img id="preview_back" src="" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Selfie</p>
                                        <img id="preview_selfie" src="" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Statut KYC -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                ✅ Statut de Vérification
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="status_kyc" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Statut KYC
                                    </label>
                                    <select
                                        id="status_kyc"
                                        name="status_kyc"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="pending" {{ old('status_kyc', $client->status_kyc) == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="verified" {{ old('status_kyc', $client->status_kyc) == 'verified' ? 'selected' : '' }}>Vérifié</option>
                                        <option value="rejected" {{ old('status_kyc', $client->status_kyc) == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                        <option value="not_verified" {{ old('status_kyc', $client->status_kyc) == 'not_verified' ? 'selected' : '' }}>Non vérifié</option>
                                    </select>
                                </div>

                                <div class="flex items-center pt-7">
                                    <input
                                        type="checkbox"
                                        id="kyc"
                                        name="kyc"
                                        value="1"
                                        {{ old('kyc', $client->kyc) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    >
                                    <label for="kyc" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                        KYC complet et validé
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('clients.show', $client) }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                                Annuler
                            </a>
                            <button type="submit" class="px-8 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-lg hover:shadow-xl">
                                Mettre à jour la vérification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Affichage conditionnel selon type de pièce
        const pieceTypeSelect = document.getElementById('piece_type');
        const nuiField = document.getElementById('nui_field');
        const permisField = document.getElementById('permis_field');
        const passportField = document.getElementById('passport_field');

        pieceTypeSelect.addEventListener('change', function() {
            const type = this.value;
            nuiField.classList.add('hidden');
            permisField.classList.add('hidden');
            passportField.classList.add('hidden');

            if (type === 'ID') {
                nuiField.classList.remove('hidden');
            } else if (type === 'Permis') {
                permisField.classList.remove('hidden');
            } else if (type === 'Passeport') {
                passportField.classList.remove('hidden');
            }
        });

        // Formatage automatique
        document.getElementById('permis_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '').slice(0, 10);
            let formatted = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1})/, '$1-$2-$3-$4').replace(/-$/, '');
            e.target.value = formatted;
        });

        document.getElementById('nui_number')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        });

        document.getElementById('nu_number')?.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').slice(0, 10).toUpperCase();
        });

        // Nettoyer les champs non utilisés avant soumission
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const pieceType = document.getElementById('piece_type').value;

            // Désactiver les champs non utilisés selon le type sélectionné
            if (pieceType !== 'ID') {
                document.getElementById('nu_number').disabled = true;
                document.getElementById('nui_number').disabled = true;
            }
            if (pieceType !== 'Permis') {
                document.getElementById('permis_number').disabled = true;
            }
            if (pieceType !== 'Passeport') {
                document.getElementById('passport_number').disabled = true;
            }
        });

        // Polling AJAX pour vérification upload
        const tokenElement = document.getElementById('upload_token');
        const token = tokenElement ? tokenElement.value : null;

        function checkUpload() {
            if (!token) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            fetch(`/clients/check-upload/${token}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.uploaded) {
                    document.getElementById('piece_id_path').value = data.path_front;
                    document.getElementById('back_path').value = data.path_back;
                    document.getElementById('selfie_path').value = data.path_selfie;
                    document.getElementById('profil_path').value = data.path_selfie;

                    document.getElementById('preview_front').src = data.url_front;
                    document.getElementById('preview_back').src = data.url_back;
                    document.getElementById('preview_selfie').src = data.url_selfie;
                    document.getElementById('preview-section').classList.remove('hidden');
                    document.getElementById('piece-id-section').classList.add('hidden');
                    document.getElementById('upload-status').classList.remove('hidden');

                    clearInterval(uploadCheckInterval);
                }
            })
            .catch(err => console.error('Erreur polling:', err));
        }

        const uploadCheckInterval = setInterval(checkUpload, 5000);
        checkUpload();
    </script>
    @endpush
</x-app-layout>
