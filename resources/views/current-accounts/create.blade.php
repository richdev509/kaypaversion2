<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('current-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                ← Retour
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Ouvrir un Compte Courant
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
            <div class="p-4 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg">
                {{ session('error') }}
            </div>
            @endif

            {{-- ─── ÉTAPE 1 : Recherche du client ─── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Étape 1 — Identifier le client
                    </h3>
                    <form method="GET" action="{{ route('current-accounts.create') }}" class="flex gap-3">
                        <input type="text" name="search"
                            value="{{ request('search') }}"
                            placeholder="Email, téléphone ou numéro client (ex: KP-001)"
                            required
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium whitespace-nowrap">
                            Rechercher
                        </button>
                    </form>
                </div>
            </div>

            {{-- ─── RÉSULTAT DE LA RECHERCHE ─── --}}
            @if($searchPerformed)

                @if(!$client)
                {{-- Client introuvable --}}
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-300 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 rounded-lg">
                    Aucun client trouvé pour <strong>« {{ request('search') }} »</strong>.
                    Vérifiez l'email, le numéro de téléphone ou l'identifiant client.
                </div>

                @elseif($client->status_kyc !== 'verified')
                {{-- Client trouvé mais KYC non vérifié --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Client identifié</h3>
                        <div class="flex items-center gap-4 mb-5 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-lg">
                                {{ strtoupper(substr($client->first_name, 0, 1)) }}{{ strtoupper(substr($client->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ $client->full_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }} {{ $client->email ? '· ' . $client->email : '' }}</p>
                                <p class="text-xs text-gray-400">ID : {{ $client->client_id ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-800 dark:text-red-200">KYC non vérifié</p>
                                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                        Ce client doit compléter la vérification KYC avant de pouvoir ouvrir un compte courant.
                                    </p>
                                    <a href="{{ route('clients.verify-kyc', $client) }}"
                                        class="mt-3 inline-block px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg font-medium">
                                        Vérifier le KYC maintenant →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @else
                {{-- ─── ÉTAPE 2 : KYC OK — Confirmation et ouverture ─── --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Étape 2 — Confirmer et ouvrir le compte
                        </h3>

                        {{-- Info client --}}
                        <div class="flex items-center gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-green-700 dark:text-green-300 font-bold text-lg">
                                {{ strtoupper(substr($client->first_name, 0, 1)) }}{{ strtoupper(substr($client->last_name, 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ $client->full_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }} {{ $client->email ? '· ' . $client->email : '' }}</p>
                                <p class="text-xs text-gray-400">ID : {{ $client->client_id ?? '—' }}</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-medium rounded-full">
                                ✓ KYC Vérifié
                            </span>
                        </div>

                        {{-- Info frais --}}
                        <div class="mb-5 p-3 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg text-sm text-blue-800 dark:text-blue-200">
                            <strong>Frais d'ouverture :</strong> {{ number_format($fraisOuverture, 2) }} GDS — prélevés maintenant, non crédités au solde du compte.
                        </div>

                        <form method="POST" action="{{ route('current-accounts.store') }}">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $client->id }}">

                            {{-- NIF/CIN --}}
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    NIF / CIN du client <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="id_nif_cin"
                                    value="{{ old('id_nif_cin') }}"
                                    placeholder="Saisir le NIF ou CIN pour confirmer l'identité"
                                    required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('id_nif_cin') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Doit correspondre exactement au NIF/CIN enregistré dans le dossier du client.</p>
                                @error('id_nif_cin')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Mode de paiement --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Mode de paiement des frais <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('payment_method') border-red-500 @enderror">
                                    <option value="">-- Choisir --</option>
                                    <option value="cash" @selected(old('payment_method') == 'cash')>Espèces (Cash)</option>
                                    <option value="moncash" @selected(old('payment_method') == 'moncash')>MonCash</option>
                                    <option value="bank_transfer" @selected(old('payment_method') == 'bank_transfer')>Virement bancaire</option>
                                </select>
                                @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-3">
                                <a href="{{ route('current-accounts.create') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm">
                                    Annuler
                                </a>
                                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                                    Ouvrir le compte courant
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

            @endif

        </div>
    </div>
</x-app-layout>
