<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Créer un Nouveau Client
            </h2>
            <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour à la liste
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

                    <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Champs cachés pour les chemins des photos -->
                        <input type="hidden" name="piece_id_path" id="piece_id_path">
                        <input type="hidden" name="back_path" id="back_path">
                        <input type="hidden" name="selfie_path" id="selfie_path">
                        <input type="hidden" name="profil_path" id="profil_path">
                        <input type="hidden" id="upload_token" value="{{ $uploadToken }}">
                        <input type="hidden" id="upload_token_profil" value="{{ $uploadTokenProfil }}">

                        <!-- Section: Informations personnelles -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                📋 Informations Personnelles
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Prénom -->
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Prénom <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="first_name"
                                        name="first_name"
                                        value="{{ old('first_name') }}"
                                        required
                                        minlength="2"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('first_name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Nom -->
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nom <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="last_name"
                                        name="last_name"
                                        value="{{ old('last_name') }}"
                                        required
                                        minlength="2"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('last_name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Deuxième prénom -->
                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Deuxième prénom
                                    </label>
                                    <input
                                        type="text"
                                        id="middle_name"
                                        name="middle_name"
                                        value="{{ old('middle_name') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('middle_name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Sexe -->
                                <div>
                                    <label for="sexe" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Sexe
                                    </label>
                                    <select
                                        id="sexe"
                                        name="sexe"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Sélectionner</option>
                                        <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                                        <option value="Autre" {{ old('sexe') == 'Autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                    @error('sexe')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Date de naissance -->
                                <div>
                                    <label for="birth_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Date de naissance
                                    </label>
                                    <input
                                        type="date"
                                        id="birth_date"
                                        name="birth_date"
                                        value="{{ old('birth_date') }}"
                                        max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('birth_date')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Lieu de naissance -->
                                <div>
                                    <label for="lieu_naissance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Lieu de naissance
                                    </label>
                                    <input
                                        type="text"
                                        id="lieu_naissance"
                                        name="lieu_naissance"
                                        value="{{ old('lieu_naissance') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('lieu_naissance')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section: Contact -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                📞 Informations de Contact
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Téléphone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Téléphone <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        placeholder="XXXXXXXX (8 chiffres)"
                                        required
                                        maxlength="8"
                                        pattern="\d{8}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p id="phone-validation" class="mt-1 text-sm hidden"></p>
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="exemple@email.com"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p id="email-validation" class="mt-1 text-sm hidden"></p>
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Code de Parrainage (optionnel) -->
                                <div class="md:col-span-2">
                                    <label for="code_parrain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Code de Parrainage (optionnel)
                                    </label>
                                    <input
                                        type="text"
                                        id="code_parrain"
                                        name="code_parrain"
                                        value="{{ old('code_parrain') }}"
                                        placeholder="AFFXXXXXX"
                                        maxlength="9"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p id="code-parrain-validation" class="mt-1 text-sm hidden"></p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        💡 Si ce client a été recommandé par un partenaire affilié, saisissez le code de parrainage ici.
                                    </p>
                                    @error('code_parrain')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section: Localisation (Cascade) -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                📍 Localisation
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <!-- Département -->
                                <div>
                                    <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Département
                                    </label>
                                    <select
                                        id="department_id"
                                        name="department_id"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Commune -->
                                <div>
                                    <label for="commune_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Commune
                                    </label>
                                    <select
                                        id="commune_id"
                                        name="commune_id"
                                        disabled
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">-- Sélectionner d'abord un département --</option>
                                    </select>
                                    @error('commune_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Ville -->
                                <div>
                                    <label for="city_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Ville
                                    </label>
                                    <select
                                        id="city_id"
                                        name="city_id"
                                        disabled
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">-- Sélectionner d'abord une commune --</option>
                                    </select>
                                    @error('city_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Adresse complète -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Adresse complète
                                </label>
                                <textarea
                                    id="address"
                                    name="address"
                                    rows="2"
                                    placeholder="Rue, quartier, section communale..."
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                >{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

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
                                        <option value="ID" {{ old('piece_type') == 'ID' ? 'selected' : '' }}>Carte d'Identité Nationale (CIN)</option>
                                        <option value="Permis" {{ old('piece_type') == 'Permis' ? 'selected' : '' }}>Permis de conduire</option>
                                        <option value="Passeport" {{ old('piece_type') == 'Passeport' ? 'selected' : '' }}>Passeport</option>
                                    </select>
                                    @error('piece_type')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Champs pour CIN (NIU) -->
                                <div id="nui_field" class="md:col-span-2 hidden">
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
                                                    maxlength="10"
                                                    placeholder="ABC1234567"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                >
                                                <p id="nu-validation" class="mt-1 text-sm hidden"></p>
                                            </div>
                                            <div>
                                                <label for="nui_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    NIU (10 chiffres)
                                                </label>
                                                <input
                                                    type="text"
                                                    id="nui_number"
                                                    name="nui_number"
                                                    maxlength="10"
                                                    pattern="\d{10}"
                                                    placeholder="0123456789"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                >
                                                <p id="nui-validation" class="mt-1 text-sm hidden"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Champs pour Permis -->
                                <div id="permis_field" class="md:col-span-2 hidden">
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
                                                maxlength="13"
                                                placeholder="123-456-789-0"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Champs pour Passeport -->
                                <div id="passport_field" class="md:col-span-2 hidden">
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
                                                maxlength="9"
                                                placeholder="AA1234567"
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
                                        value="{{ old('date_emission') }}"
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
                                        value="{{ old('date_expiration') }}"
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

                            <div id="piece-id-section">
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 rounded-lg border-2 border-blue-200 dark:border-blue-800">
                                    <div class="flex flex-col md:flex-row items-center gap-6">
                                        <div class="flex-shrink-0">
                                            {!! QrCode::size(150)->generate(route('clients.scan', ['token' => $uploadToken])) !!}
                                        </div>
                                        <div class="flex-1 text-center md:text-left">
                                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                                                📱 Scannez ce QR Code avec un téléphone
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

                            <!-- Statut upload (caché par défaut) -->
                            <div id="upload-status" class="hidden mt-4">
                                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-sm font-medium text-green-800 dark:text-green-200">Photos reçues avec succès!</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Prévisualisations (cachées par défaut) -->
                            <div id="preview-section" class="hidden mt-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Aperçu des photos :</h4>
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
                                        <option value="pending" {{ old('status_kyc', 'pending') == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="verified" {{ old('status_kyc') == 'verified' ? 'selected' : '' }}>Vérifié</option>
                                        <option value="rejected" {{ old('status_kyc') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                        <option value="not_verified" {{ old('status_kyc') == 'not_verified' ? 'selected' : '' }}>Non vérifié</option>
                                    </select>
                                </div>

                                <div class="flex items-center pt-7">
                                    <input
                                        type="checkbox"
                                        id="kyc"
                                        name="kyc"
                                        value="1"
                                        {{ old('kyc') ? 'checked' : '' }}
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
                            <a href="{{ route('clients.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                                Annuler
                            </a>
                            <button type="submit" id="submit-btn" class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-lg hover:shadow-xl">
                                Créer le client
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        console.log('=== Script chargé ===');

        // ==========================================
        // 1. CASCADE GÉOGRAPHIQUE (Département → Commune → Ville)
        // ==========================================
        const departmentSelect = document.getElementById('department_id');
        const communeSelect = document.getElementById('commune_id');
        const citySelect = document.getElementById('city_id');

        console.log('Elements cascade:', {
            departmentSelect: !!departmentSelect,
            communeSelect: !!communeSelect,
            citySelect: !!citySelect
        });

        departmentSelect.addEventListener('change', function() {
            const departmentId = this.value;

            // Réinitialiser communes et villes
            communeSelect.innerHTML = '<option value="">-- Chargement... --</option>';
            communeSelect.disabled = true;
            citySelect.innerHTML = '<option value="">-- Sélectionner d\'abord une commune --</option>';
            citySelect.disabled = true;

            if (departmentId) {
                fetch(`/get-communes/${departmentId}`)
                    .then(res => res.json())
                    .then(data => {
                        communeSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
                        data.forEach(commune => {
                            communeSelect.innerHTML += `<option value="${commune.id}">${commune.name}</option>`;
                        });
                        communeSelect.disabled = false;
                    })
                    .catch(err => {
                        console.error('Erreur:', err);
                        communeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    });
            }
        });

        communeSelect.addEventListener('change', function() {
            const communeId = this.value;

            citySelect.innerHTML = '<option value="">-- Chargement... --</option>';
            citySelect.disabled = true;

            if (communeId) {
                fetch(`/get-cities/${communeId}`)
                    .then(res => res.json())
                    .then(data => {
                        citySelect.innerHTML = '<option value="">-- Sélectionner --</option>';
                        data.forEach(city => {
                            citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                        citySelect.disabled = false;
                    })
                    .catch(err => {
                        console.error('Erreur:', err);
                        citySelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    });
            }
        });

        // ==========================================
        // 2. AFFICHAGE CONDITIONNEL SELON TYPE DE PIÈCE
        // ==========================================
        const pieceTypeSelect = document.getElementById('piece_type');
        const nuiField = document.getElementById('nui_field');
        const permisField = document.getElementById('permis_field');
        const passportField = document.getElementById('passport_field');

        pieceTypeSelect.addEventListener('change', function() {
            const type = this.value;

            // Masquer tous les champs
            nuiField.classList.add('hidden');
            permisField.classList.add('hidden');
            passportField.classList.add('hidden');

            // Afficher le champ correspondant
            if (type === 'ID') {
                nuiField.classList.remove('hidden');
            } else if (type === 'Permis') {
                permisField.classList.remove('hidden');
            } else if (type === 'Passeport') {
                passportField.classList.remove('hidden');
            }
        });

        // ==========================================
        // 3. FORMATAGE AUTOMATIQUE NUMÉRO DE PERMIS
        // ==========================================
        const permisInput = document.getElementById('permis_number');

        permisInput.addEventListener('input', function(e) {
            // Retirer tous les non-chiffres
            let value = e.target.value.replace(/\D/g, '').slice(0, 10);

            // Formater avec tirets: 123-456-789-0
            let formatted = value
                .replace(/(\d{3})(\d{3})(\d{3})(\d{1})/, '$1-$2-$3-$4')
                .replace(/-$/, '');

            e.target.value = formatted;
        });

        // ==========================================
        // 4. POLLING AJAX - VÉRIFICATION UPLOAD PHOTOS
        // ==========================================
        const tokenElement = document.getElementById('upload_token');

        console.log('Element token:', !!tokenElement);

        if (!tokenElement) {
            console.error('ERREUR: Element upload_token introuvable!');
        }

        const token = tokenElement ? tokenElement.value : null;

        console.log('Token de scan:', token);

        if (!token) {
            console.error('ERREUR: Token vide ou null!');
        }

        function checkUpload() {
            console.log('=== Début checkUpload ===');
            console.log('Vérification upload pour token:', token);

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            console.log('CSRF Token element:', !!csrfToken);
            console.log('CSRF Token value:', csrfToken ? csrfToken.content : 'MISSING');

            fetch(`/clients/check-upload/${token}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    console.log('Réponse HTTP reçue:', res.status, res.statusText);
                    if (!res.ok) {
                        console.error('Erreur HTTP:', res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Données JSON:', data);
                    if (data.uploaded) {
                        console.log('✅ PHOTOS TROUVÉES!');
                        // Photos uploadées avec succès

                        // Remplir les champs cachés
                        document.getElementById('piece_id_path').value = data.path_front;
                        document.getElementById('back_path').value = data.path_back;
                        document.getElementById('selfie_path').value = data.path_selfie;
                        document.getElementById('profil_path').value = data.path_selfie;

                        // Afficher prévisualisation
                        document.getElementById('preview_front').src = data.url_front;
                        document.getElementById('preview_back').src = data.url_back;
                        document.getElementById('preview_selfie').src = data.url_selfie;
                        document.getElementById('preview-section').classList.remove('hidden');

                        // Masquer le QR Code
                        document.getElementById('piece-id-section').classList.add('hidden');

                        // Afficher message de succès
                        document.getElementById('upload-status').classList.remove('hidden');

                        // Arrêter le polling
                        clearInterval(uploadCheckInterval);
                        console.log('✅ Polling arrêté - photos chargées');
                    } else {
                        console.log('❌ Pas encore de photos uploadées');
                    }
                })
                .catch(err => {
                    console.error('❌ ERREUR lors du polling:', err);
                    console.error('Stack:', err.stack);
                });
        }

        // Vérifier toutes les 5 secondes
        console.log('Démarrage du polling...');
        const uploadCheckInterval = setInterval(checkUpload, 5000);

        // Vérifier immédiatement au chargement
        console.log('Première vérification immédiate...');
        checkUpload();

        // ==========================================
        // 5. VALIDATION CLIENT-SIDE AMÉLIORÉE
        // ==========================================

        // Variables de validation pour tous les champs
        let nuIsValid = true;
        let nuiIsValid = true;

        // Limiter NIU à 10 chiffres uniquement
        const nuiInput = document.getElementById('nui_number');
        const nuiValidation = document.getElementById('nui-validation');
        let nuiTimeout;

        if (nuiInput) {
            nuiInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
                const value = e.target.value;

                clearTimeout(nuiTimeout);

                if (value.length === 10) {
                    nuiTimeout = setTimeout(() => {
                        checkDocumentUnique('nui_number', value, nuiValidation, 'nui');
                    }, 500);
                } else if (value.length > 0 && value.length < 10) {
                    nuiValidation.textContent = 'Le NIU doit contenir exactement 10 chiffres';
                    nuiValidation.className = 'mt-1 text-sm text-orange-600 dark:text-orange-400';
                    nuiValidation.classList.remove('hidden');
                    nuiIsValid = false;
                    updateSubmitButton();
                } else {
                    nuiValidation.classList.add('hidden');
                    nuiIsValid = true;
                    updateSubmitButton();
                }
            });
        }

        // Numéro carte: 10 caractères alphanumériques
        const nuInput = document.getElementById('nu_number');
        const nuValidation = document.getElementById('nu-validation');
        let nuTimeout;

        if (nuInput) {
            nuInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^a-zA-Z0-9]/g, '').slice(0, 10).toUpperCase();
                const value = e.target.value;

                clearTimeout(nuTimeout);

                if (value.length === 10) {
                    nuTimeout = setTimeout(() => {
                        checkDocumentUnique('nu_number', value, nuValidation, 'nu');
                    }, 500);
                } else if (value.length > 0 && value.length < 10) {
                    nuValidation.textContent = 'Le numéro de carte doit contenir exactement 10 caractères';
                    nuValidation.className = 'mt-1 text-sm text-orange-600 dark:text-orange-400';
                    nuValidation.classList.remove('hidden');
                    nuIsValid = false;
                    updateSubmitButton();
                } else {
                    nuValidation.classList.add('hidden');
                    nuIsValid = true;
                    updateSubmitButton();
                }
            });
        }

        // Fonction pour vérifier l'unicité des documents
        function checkDocumentUnique(field, value, element, fieldType) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            fetch('/clients/check-document', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field: field,
                    value: value
                })
            })
            .then(res => res.json())
            .then(data => {
                element.textContent = data.message;
                if (data.available) {
                    element.className = 'mt-1 text-sm text-green-600 dark:text-green-400';
                    if (fieldType === 'nu') nuIsValid = true;
                    if (fieldType === 'nui') nuiIsValid = true;
                } else {
                    element.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                    if (fieldType === 'nu') nuIsValid = false;
                    if (fieldType === 'nui') nuiIsValid = false;
                }
                element.classList.remove('hidden');
                updateSubmitButton();
            })
            .catch(err => {
                console.error('Erreur vérification document:', err);
            });
        }

        // ==========================================
        // 6. FORMATAGE ET VALIDATION TÉLÉPHONE (8 chiffres)
        // ==========================================
        const phoneInput = document.getElementById('phone');
        const phoneValidation = document.getElementById('phone-validation');
        const submitBtn = document.getElementById('submit-btn');
        let phoneTimeout;
        let phoneIsValid = true; // Par défaut valide (vide autorisé)
        let emailIsValid = true; // Par défaut valide (vide autorisé)

        phoneInput.addEventListener('input', function(e) {
            // Retirer tous les non-chiffres et limiter à 8
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 8);

            const phone = e.target.value;

            // Réinitialiser le timeout
            clearTimeout(phoneTimeout);

            if (phone.length === 8) {
                // Attendre 500ms après la dernière frappe avant de vérifier
                phoneTimeout = setTimeout(() => {
                    checkFieldUnique('phone', phone, phoneValidation, 'phone');
                }, 500);
            } else if (phone.length > 0 && phone.length < 8) {
                phoneValidation.textContent = 'Le numéro doit contenir exactement 8 chiffres';
                phoneValidation.className = 'mt-1 text-sm text-orange-600 dark:text-orange-400';
                phoneValidation.classList.remove('hidden');
                phoneIsValid = false;
                updateSubmitButton();
            } else {
                phoneValidation.classList.add('hidden');
                phoneIsValid = true;
                updateSubmitButton();
            }
        });

        // ==========================================
        // 7. VALIDATION EMAIL EN TEMPS RÉEL
        // ==========================================
        const emailInput = document.getElementById('email');
        const emailValidation = document.getElementById('email-validation');
        let emailTimeout;

        emailInput.addEventListener('input', function(e) {
            const email = e.target.value;

            clearTimeout(emailTimeout);

            if (email.length > 3 && email.includes('@')) {
                emailTimeout = setTimeout(() => {
                    checkFieldUnique('email', email, emailValidation, 'email');
                }, 500);
            } else if (email.length > 0) {
                emailValidation.classList.add('hidden');
                emailIsValid = true;
                updateSubmitButton();
            } else {
                emailValidation.classList.add('hidden');
                emailIsValid = true;
                updateSubmitButton();
            }
        });

        // ==========================================
        // 7.5. VALIDATION CODE PARRAINAGE EN TEMPS RÉEL
        // ==========================================
        const codeParrainInput = document.getElementById('code_parrain');
        const codeParrainValidation = document.getElementById('code-parrain-validation');
        let codeParrainTimeout;
        let codeParrainIsValid = true; // Par défaut valide (optionnel)

        if (codeParrainInput) {
            codeParrainInput.addEventListener('input', function(e) {
                // Convertir en majuscules
                e.target.value = e.target.value.toUpperCase();
                const code = e.target.value.trim();

                clearTimeout(codeParrainTimeout);

                if (code.length > 0) {
                    codeParrainTimeout = setTimeout(() => {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]');

                        fetch('/clients/check-code-parrain', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ code: code })
                        })
                        .then(res => res.json())
                        .then(data => {
                            codeParrainValidation.textContent = data.message;
                            if (data.valid) {
                                codeParrainValidation.className = 'mt-1 text-sm text-green-600 dark:text-green-400';
                                codeParrainIsValid = true;
                            } else {
                                codeParrainValidation.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                                codeParrainIsValid = false;
                            }
                            codeParrainValidation.classList.remove('hidden');
                            updateSubmitButton();
                        })
                        .catch(err => {
                            console.error('Erreur vérification code parrain:', err);
                        });
                    }, 500);
                } else {
                    codeParrainValidation.classList.add('hidden');
                    codeParrainIsValid = true;
                    updateSubmitButton();
                }
            });
        }

        // ==========================================
        // 8. FONCTION DE VÉRIFICATION UNICITÉ AJAX
        // ==========================================
        function checkFieldUnique(field, value, element, fieldType) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            fetch('/clients/check-unique', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field: field,
                    value: value
                })
            })
            .then(res => res.json())
            .then(data => {
                element.textContent = data.message;
                if (data.available) {
                    element.className = 'mt-1 text-sm text-green-600 dark:text-green-400';
                    if (fieldType === 'phone') phoneIsValid = true;
                    if (fieldType === 'email') emailIsValid = true;
                } else {
                    element.className = 'mt-1 text-sm text-red-600 dark:text-red-400';
                    if (fieldType === 'phone') phoneIsValid = false;
                    if (fieldType === 'email') emailIsValid = false;
                }
                element.classList.remove('hidden');
                updateSubmitButton();
            })
            .catch(err => {
                console.error('Erreur vérification unicité:', err);
            });
        }

        // ==========================================
        // 9. BLOQUER SOUMISSION SI VALIDATION ÉCHOUE
        // ==========================================
        function updateSubmitButton() {
            if (!phoneIsValid || !emailIsValid || !codeParrainIsValid || !nuIsValid || !nuiIsValid) {
                submitBtn.disabled = true;
                submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                submitBtn.title = 'Veuillez corriger les erreurs avant de soumettre';
            } else {
                submitBtn.disabled = false;
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                submitBtn.title = '';
            }
        }

        // Bloquer soumission du formulaire si validation échoue
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!phoneIsValid || !emailIsValid || !codeParrainIsValid || !nuIsValid || !nuiIsValid) {
                e.preventDefault();
                alert('⚠️ Veuillez corriger les erreurs avant de soumettre le formulaire:\n\n' +
                      (!phoneIsValid ? '- Le numéro de téléphone est déjà utilisé ou invalide\n' : '') +
                      (!emailIsValid ? '- L\'adresse email est déjà utilisée\n' : '') +
                      (!codeParrainIsValid ? '- Le code de parrainage est invalide\n' : '') +
                      (!nuIsValid ? '- Le numéro de carte est déjà utilisé\n' : '') +
                      (!nuiIsValid ? '- Le NIU est déjà utilisé\n' : ''));
                return false;
            }
        });
    </script>
    @endpush
</x-app-layout>
