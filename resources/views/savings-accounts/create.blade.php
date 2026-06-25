<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('savings-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">← Retour</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ouvrir un Compte Épargne</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
            <div class="p-4 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
            @endif

            {{-- Étape 1 : Recherche --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Étape 1 — Identifier le client</h3>
                <form method="GET" action="{{ route('savings-accounts.create') }}" class="flex gap-3">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Email, téléphone ou numéro client (ex: KP-001)"
                        required
                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium whitespace-nowrap">Rechercher</button>
                </form>
            </div>

            @if($searchPerformed)
                @if(!$client)
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-300 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 rounded-lg">
                    Aucun client trouvé pour <strong>« {{ request('search') }} »</strong>.
                </div>

                @elseif($client->status_kyc !== 'verified')
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Client identifié</h3>
                    <div class="flex items-center gap-4 mb-5 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center text-emerald-700 dark:text-emerald-300 font-bold text-lg">
                            {{ strtoupper(substr($client->first_name, 0, 1)) }}{{ strtoupper(substr($client->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ $client->full_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }}</p>
                        </div>
                    </div>
                    <div class="p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-200">KYC non vérifié</p>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">Ce client doit compléter la vérification KYC avant d'ouvrir un compte épargne.</p>
                        <a href="{{ route('clients.verify-kyc', $client) }}" class="mt-3 inline-block px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg font-medium">Vérifier le KYC →</a>
                    </div>
                </div>

                @else
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Étape 2 — Confirmer et ouvrir le compte</h3>

                    <div class="flex items-center gap-4 mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center text-emerald-700 dark:text-emerald-300 font-bold text-lg">
                            {{ strtoupper(substr($client->first_name, 0, 1)) }}{{ strtoupper(substr($client->last_name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ $client->full_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->phone }} {{ $client->email ? '· ' . $client->email : '' }}</p>
                        </div>
                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-medium rounded-full">✓ KYC Vérifié</span>
                    </div>

                    @if($fraisOuverture > 0)
                    <div class="mb-5 p-3 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg text-sm text-blue-800 dark:text-blue-200">
                        <strong>Frais d'ouverture :</strong> {{ number_format($fraisOuverture, 2) }} HTG
                    </div>
                    @else
                    <div class="mb-5 p-3 bg-emerald-50 dark:bg-emerald-900 border border-emerald-200 dark:border-emerald-700 rounded-lg text-sm text-emerald-800 dark:text-emerald-200">
                        ✓ Ouverture gratuite — aucun frais.
                    </div>
                    @endif

                    <form method="POST" action="{{ route('savings-accounts.store') }}">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">NIF / CIN du client <span class="text-red-500">*</span></label>
                            <input type="text" name="id_nif_cin" value="{{ old('id_nif_cin') }}"
                                placeholder="Saisir le NIF ou CIN pour confirmer l'identité" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 @error('id_nif_cin') border-red-500 @enderror">
                            @error('id_nif_cin')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dépôt initial (GDS) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="initial_deposit" value="{{ old('initial_deposit', $soldeMinimum) }}"
                                min="{{ $soldeMinimum }}" step="0.01" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 @error('initial_deposit') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Minimum obligatoire : <strong>{{ number_format($soldeMinimum, 2) }} GDS</strong> — ce montant constitue le solde plancher du compte.
                            </p>
                            @error('initial_deposit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode de paiement <span class="text-red-500">*</span></label>
                            <select name="payment_method" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">-- Choisir --</option>
                                <option value="cash"          @selected(old('payment_method') == 'cash')>Espèces (Cash)</option>
                                <option value="moncash"       @selected(old('payment_method') == 'moncash')>MonCash</option>
                                <option value="bank_transfer" @selected(old('payment_method') == 'bank_transfer')>Virement bancaire</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('savings-accounts.create') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm">Annuler</a>
                            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium">Ouvrir le compte épargne</button>
                        </div>
                    </form>
                </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
