<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Modifier le Client: {{ $client->full_name }}
            </h2>
            <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour à la liste
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                    <form action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Section: Informations personnelles -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Informations Personnelles
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
                                        value="{{ old('first_name', $client->first_name) }}"
                                        required
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
                                        value="{{ old('last_name', $client->last_name) }}"
                                        required
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
                                        value="{{ old('middle_name', $client->middle_name) }}"
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
                                        <option value="M" {{ old('sexe', $client->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe', $client->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                                        <option value="Autre" {{ old('sexe', $client->sexe) == 'Autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                    @error('sexe')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Date de naissance -->
                                <div>
                                    <label for="date_naissance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Date de naissance
                                    </label>
                                    <input
                                        type="date"
                                        id="date_naissance"
                                        name="date_naissance"
                                        value="{{ old('date_naissance', $client->date_naissance?->format('Y-m-d')) }}"
                                        max="{{ date('Y-m-d') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('date_naissance')
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
                                        value="{{ old('lieu_naissance', $client->lieu_naissance) }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('lieu_naissance')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Nationalité -->
                                <div>
                                    <label for="nationalite" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nationalité
                                    </label>
                                    <input
                                        type="text"
                                        id="nationalite"
                                        name="nationalite"
                                        value="{{ old('nationalite', $client->nationalite ?? 'Haïtienne') }}"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('nationalite')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section: Contact -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Informations de Contact
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
                                        value="{{ old('phone', $client->phone) }}"
                                        placeholder="+509 XXXX-XXXX"
                                        required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
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
                                        value="{{ old('email', $client->email) }}"
                                        placeholder="exemple@email.com"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Adresse -->
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Adresse complète
                                    </label>
                                    <textarea
                                        id="address"
                                        name="address"
                                        rows="2"
                                        placeholder="Rue, quartier, ville..."
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    >{{ old('address', $client->address) }}</textarea>
                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>



                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                                Annuler
                            </a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
