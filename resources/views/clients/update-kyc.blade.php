<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📸 Mise à jour KYC - {{ $client->full_name }}
            </h2>
            <a href="{{ route('clients.show', $client) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Avertissement -->
            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            Mise à jour des documents d'identité
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Vous devez obligatoirement scanner les <strong>3 photos</strong> : Recto, Verso et Selfie</li>
                                <li>Les anciennes photos seront remplacées par les nouvelles</li>
                                <li>Le statut KYC passera en "En attente" après la mise à jour</li>
                                <li>Vérifiez bien que les informations sont identiques au document d'identité</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('clients.process-kyc-update', $client) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Type de document -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type de document d'identité <span class="text-red-500">*</span>
                        </label>
                        <select name="document_id_type" id="document_id_type" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">-- Sélectionner --</option>
                            <option value="ID" {{ old('document_id_type', $client->document_id_type) === 'ID' ? 'selected' : '' }}>
                                🪪 Carte d'identité nationale (CIN)
                            </option>
                            <option value="Permis" {{ old('document_id_type', $client->document_id_type) === 'Permis' ? 'selected' : '' }}>
                                🚗 Permis de conduire
                            </option>
                            <option value="Passeport" {{ old('document_id_type', $client->document_id_type) === 'Passeport' ? 'selected' : '' }}>
                                🛂 Passeport
                            </option>
                        </select>
                        @error('document_id_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Champs conditionnels selon le type -->
                    <div id="id-fields" class="hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Numéro de carte (10 caractères) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nu_number" id="nu_number"
                                       value="{{ old('nu_number', $client->numero_carte) }}"
                                       placeholder="ABC1234567"
                                       maxlength="10"
                                       style="text-transform: uppercase;"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <p class="mt-1 text-xs text-gray-500">Format: Lettres + chiffres (10 caractères, ex: ABC1234567)</p>
                                @error('nu_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    NIU (10 chiffres) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nui_number" id="nui_number"
                                       value="{{ old('nui_number', $client->id_nif_cin) }}"
                                       placeholder="0123456789"
                                       pattern="[0-9]{10}"
                                       maxlength="10"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <p class="mt-1 text-xs text-gray-500">10 chiffres uniquement (ex: 0123456789)</p>
                                @error('nui_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div id="permis-fields" class="hidden mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Numéro de permis de conduire <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="permis_number" id="permis_number"
                               value="{{ old('permis_number') }}"
                               placeholder="AB12345678901"
                               pattern="[A-Z]{2}[0-9]{11}"
                               maxlength="13"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <p class="mt-1 text-xs text-gray-500">Format: 2 lettres + 11 chiffres (ex: AB12345678901)</p>
                        @error('permis_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="passport-fields" class="hidden mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Numéro de passeport <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="passport_number" id="passport_number"
                               value="{{ old('passport_number') }}"
                               placeholder="AB1234567"
                               pattern="[A-Z]{2}[0-9]{7}"
                               maxlength="9"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <p class="mt-1 text-xs text-gray-500">Format: 2 lettres + 7 chiffres (ex: AB1234567)</p>
                        @error('passport_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Date d'émission <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_emission" required
                                   value="{{ old('date_emission', $client->date_emission?->format('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('date_emission')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Date d'expiration <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_expiration" required
                                   value="{{ old('date_expiration', $client->date_expiration?->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('date_expiration')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Section Scan QR Code -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            📸 Scanner les 3 photos avec votre téléphone
                        </h3>

                        <div class="bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 p-6 rounded-lg border-2 border-purple-200 dark:border-purple-800">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                <div class="flex-shrink-0">
                                    {!! QrCode::size(180)->generate(route('clients.scan', ['token' => $uploadToken])) !!}
                                </div>
                                <div class="flex-1 text-center md:text-left">
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                                        📱 Scannez ce QR Code avec un téléphone
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        Vous serez invité à prendre 3 photos séquentiellement :
                                    </p>
                                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 text-left">
                                        <li>1️⃣ Photo RECTO de la pièce d'identité</li>
                                        <li>2️⃣ Photo VERSO de la pièce d'identité</li>
                                        <li>3️⃣ Selfie du client</li>
                                    </ul>
                                    <div class="mt-4 flex items-center justify-center md:justify-start gap-2">
                                        <div class="animate-pulse">
                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <span id="scan-status" class="text-xs text-gray-500 dark:text-gray-400">En attente de scan...</span>
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
                                    <span class="text-sm font-medium text-green-800 dark:text-green-200">✅ Les 3 photos ont été reçues avec succès!</span>
                                </div>
                            </div>
                        </div>

                        <!-- Prévisualisations -->
                        <div id="preview-section" class="hidden mt-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Aperçu des photos :</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Recto</p>
                                    <img id="preview_front" src="" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Verso</p>
                                    <img id="preview_back" src="" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Selfie</p>
                                    <img id="preview_selfie" src="" class="w-full h-32 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                                </div>
                            </div>
                        </div>

                        <!-- Champs cachés -->
                        <input type="hidden" name="front_path" id="front_path" required>
                        <input type="hidden" name="back_path" id="back_path" required>
                        <input type="hidden" name="selfie_path" id="selfie_path" required>
                        @error('front_path')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('back_path')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('selfie_path')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Boutons -->
                    <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('clients.show', $client) }}"
                           class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                            Annuler
                        </a>
                        <button type="submit" id="submit-btn" disabled
                                class="px-8 py-2.5 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition shadow-lg">
                            💾 Mettre à jour le KYC
                        </button>
                    </div>
                    <div id="validation-errors" class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">⚠️ Erreurs de validation :</h4>
                        <ul id="error-list" class="text-sm text-red-700 dark:text-red-300 list-disc list-inside space-y-1"></ul>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const uploadToken = @json($uploadToken);
        const baseUrl = @json(url('/'));

        // Variables pour suivre les uploads
        let uploadedFiles = {
            front: false,
            back: false,
            selfie: false
        };

        // Polling pour vérifier l'upload toutes les 2 secondes
        const checkInterval = setInterval(async () => {
            try {
                const response = await fetch(`${baseUrl}/clients/check-upload/${uploadToken}`);
                const data = await response.json();

                console.log('Vérification upload:', data);

                if (data.uploaded) {
                    // Mettre à jour les champs cachés
                    if (data.path_front) {
                        document.getElementById('front_path').value = data.path_front;
                        document.getElementById('preview_front').src = `${baseUrl}/storage/${data.path_front}`;
                        uploadedFiles.front = true;
                    }
                    if (data.path_back) {
                        document.getElementById('back_path').value = data.path_back;
                        document.getElementById('preview_back').src = `${baseUrl}/storage/${data.path_back}`;
                        uploadedFiles.back = true;
                    }
                    if (data.path_selfie) {
                        document.getElementById('selfie_path').value = data.path_selfie;
                        document.getElementById('preview_selfie').src = `${baseUrl}/storage/${data.path_selfie}`;
                        uploadedFiles.selfie = true;
                    }

                    // Vérifier si toutes les photos sont uploadées
                    if (uploadedFiles.front && uploadedFiles.back && uploadedFiles.selfie) {
                        clearInterval(checkInterval);

                        // Afficher le statut de succès
                        document.getElementById('upload-status').classList.remove('hidden');
                        document.getElementById('preview-section').classList.remove('hidden');
                        document.getElementById('scan-status').textContent = '✅ Toutes les photos reçues!';
                        document.getElementById('scan-status').classList.remove('text-gray-500');
                        document.getElementById('scan-status').classList.add('text-green-600', 'font-semibold');

                        // Activer le bouton de soumission
                        document.getElementById('submit-btn').disabled = false;
                        document.getElementById('submit-btn').classList.remove('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
                    }
                }
            } catch (error) {
                console.error('Erreur vérification upload:', error);
            }
        }, 2000);

        // Gestion des champs conditionnels
        const docTypeSelect = document.getElementById('document_id_type');
        const idFields = document.getElementById('id-fields');
        const permisFields = document.getElementById('permis-fields');
        const passportFields = document.getElementById('passport-fields');

        function toggleFields() {
            const type = docTypeSelect.value;

            idFields.classList.add('hidden');
            permisFields.classList.add('hidden');
            passportFields.classList.add('hidden');

            // Désactiver tous les required
            document.getElementById('nu_number').required = false;
            document.getElementById('nui_number').required = false;
            document.getElementById('permis_number').required = false;
            document.getElementById('passport_number').required = false;

            if (type === 'ID') {
                idFields.classList.remove('hidden');
                document.getElementById('nu_number').required = true;
                document.getElementById('nui_number').required = true;
            } else if (type === 'Permis') {
                permisFields.classList.remove('hidden');
                document.getElementById('permis_number').required = true;
            } else if (type === 'Passeport') {
                passportFields.classList.remove('hidden');
                document.getElementById('passport_number').required = true;
            }
        }

        docTypeSelect.addEventListener('change', function() {
            // Vider tous les champs avant de changer
            document.getElementById('nu_number').value = '';
            document.getElementById('nui_number').value = '';
            document.getElementById('permis_number').value = '';
            document.getElementById('passport_number').value = '';

            toggleFields();
        });

        // Initialiser au chargement
        toggleFields();

        // Validation avant soumission
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const errors = [];
            const errorList = document.getElementById('error-list');
            const validationErrors = document.getElementById('validation-errors');

            // Vérifier que les 3 images sont uploadées
            if (!uploadedFiles.front || !uploadedFiles.back || !uploadedFiles.selfie) {
                errors.push('Les 3 photos (Recto, Verso, Selfie) sont obligatoires');
            }

            // Vérifier le type de document
            const docType = docTypeSelect.value;
            if (!docType) {
                errors.push('Le type de document est obligatoire');
            }

            // Vérifier les champs selon le type
            if (docType === 'ID') {
                const nuNumber = document.getElementById('nu_number').value.toUpperCase();
                const nuiNumber = document.getElementById('nui_number').value;

                if (!nuNumber || nuNumber.length !== 10 || !/^[A-Z0-9]{10}$/.test(nuNumber)) {
                    errors.push('Le numéro de carte doit contenir exactement 10 caractères (lettres et/ou chiffres)');
                }
                if (!nuiNumber || nuiNumber.length !== 10 || !/^[0-9]{10}$/.test(nuiNumber)) {
                    errors.push('Le NIU doit contenir exactement 10 chiffres');
                }
            } else if (docType === 'Permis') {
                const permisNumber = document.getElementById('permis_number').value;
                if (!permisNumber || permisNumber.length !== 13 || !/^[A-Z]{2}[0-9]{11}$/.test(permisNumber)) {
                    errors.push('Le numéro de permis doit avoir le format: 2 lettres + 11 chiffres (ex: AB12345678901)');
                }
            } else if (docType === 'Passeport') {
                const passportNumber = document.getElementById('passport_number').value;
                if (!passportNumber || passportNumber.length !== 9 || !/^[A-Z]{2}[0-9]{7}$/.test(passportNumber)) {
                    errors.push('Le numéro de passeport doit avoir le format: 2 lettres + 7 chiffres (ex: AB1234567)');
                }
            }

            // Vérifier les dates
            const dateEmission = document.querySelector('input[name="date_emission"]').value;
            const dateExpiration = document.querySelector('input[name="date_expiration"]').value;

            if (!dateEmission) {
                errors.push('La date d\'émission est obligatoire');
            }
            if (!dateExpiration) {
                errors.push('La date d\'expiration est obligatoire');
            }

            if (dateEmission && dateExpiration && new Date(dateExpiration) <= new Date(dateEmission)) {
                errors.push('La date d\'expiration doit être après la date d\'émission');
            }

            // Afficher les erreurs ou soumettre
            if (errors.length > 0) {
                errorList.innerHTML = errors.map(err => `<li>${err}</li>`).join('');
                validationErrors.classList.remove('hidden');

                // Scroll vers les erreurs
                validationErrors.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // Tout est bon, soumettre le formulaire
                validationErrors.classList.add('hidden');
                form.submit();
            }
        });
    </script>
    @endpush
</x-app-layout>
