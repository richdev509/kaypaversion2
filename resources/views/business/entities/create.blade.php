<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.entities.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Nouveau Business
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">

                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-sm text-red-700 dark:text-red-400">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('business.entities.store') }}" class="space-y-5" id="business-form">
                    @csrf

                    {{-- Recherche du client propriétaire --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Client propriétaire <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <input type="text" id="client-search-input"
                                autocomplete="off"
                                placeholder="Rechercher par nom, téléphone ou carte KAYPA..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 pr-8 focus:ring-indigo-500 focus:border-indigo-500"
                                value="{{ old('_client_search_label') }}">

                            <div id="client-search-spinner" class="hidden absolute right-3 top-2.5">
                                <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                            </div>

                            {{-- Dropdown résultats --}}
                            <ul id="client-search-results"
                                class="hidden absolute z-20 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            </ul>
                        </div>

                        {{-- Champ caché soumis au contrôleur --}}
                        <input type="hidden" name="owner_client_id" id="owner_client_id" value="{{ old('owner_client_id') }}">

                        @error('owner_client_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        {{-- Carte du client sélectionné --}}
                        <div id="client-selected-card" class="hidden mt-2 p-3 rounded-lg border flex items-start gap-3">
                            <div id="client-kyc-icon" class="flex-shrink-0 mt-0.5"></div>
                            <div class="flex-1 min-w-0">
                                <p id="client-selected-name" class="text-sm font-semibold text-gray-900 dark:text-gray-100"></p>
                                <p id="client-selected-meta" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"></p>
                                <p id="client-kyc-warning" class="hidden mt-1 text-xs font-medium text-red-600 dark:text-red-400">
                                    KYC non vérifié — ce client ne peut pas être propriétaire d'un business.
                                </p>
                            </div>
                            <button type="button" id="client-clear-btn"
                                class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg leading-none"
                                title="Changer de client">×</button>
                        </div>
                    </div>

                    {{-- Nom commercial --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nom commercial <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            required maxlength="255"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Raison sociale --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Raison sociale</label>
                        <input type="text" name="legal_name" value="{{ old('legal_name') }}" maxlength="255"
                            placeholder="Optionnel — nom légal si différent du nom commercial"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Profil crédit --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Profil crédit <span class="text-red-500">*</span>
                        </label>
                        <select name="profile" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="standard" @selected(old('profile', 'standard') === 'standard')>Standard</option>
                            <option value="etabli"   @selected(old('profile') === 'etabli')>Établi</option>
                            <option value="premium"  @selected(old('profile') === 'premium')>Premium</option>
                        </select>
                    </div>

                    {{-- Adresse --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Ville --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville</label>
                        <input type="text" name="city" value="{{ old('city') }}" maxlength="100"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Téléphone + Email --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" maxlength="20"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- RCCM + NIF --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RCCM</label>
                            <input type="text" name="rccm" value="{{ old('rccm') }}" maxlength="100"
                                placeholder="N° registre de commerce"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIF</label>
                            <input type="text" name="nif" value="{{ old('nif') }}" maxlength="50"
                                placeholder="N° identifiant fiscal"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('business.entities.index') }}"
                           class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Annuler
                        </a>
                        <button type="submit" id="submit-btn"
                            class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed">
                            Créer le Business
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const searchInput   = document.getElementById('client-search-input');
        const hiddenInput   = document.getElementById('owner_client_id');
        const resultsList   = document.getElementById('client-search-results');
        const spinner       = document.getElementById('client-search-spinner');
        const selectedCard  = document.getElementById('client-selected-card');
        const selectedName  = document.getElementById('client-selected-name');
        const selectedMeta  = document.getElementById('client-selected-meta');
        const kycIcon       = document.getElementById('client-kyc-icon');
        const kycWarning    = document.getElementById('client-kyc-warning');
        const clearBtn      = document.getElementById('client-clear-btn');
        const submitBtn     = document.getElementById('submit-btn');

        let debounceTimer = null;

        const KYC_OK_ICON = `<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`;

        const KYC_NOK_ICON = `<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>`;

        function selectClient(client) {
            hiddenInput.value = client.id;
            searchInput.value = client.label;
            resultsList.classList.add('hidden');

            selectedName.textContent = client.label;
            selectedMeta.textContent = [client.phone, client.client_id].filter(Boolean).join(' · ');

            if (client.kyc_ok) {
                selectedCard.className = 'mt-2 p-3 rounded-lg border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 flex items-start gap-3';
                kycIcon.innerHTML = KYC_OK_ICON;
                kycWarning.classList.add('hidden');
                submitBtn.disabled = false;
            } else {
                selectedCard.className = 'mt-2 p-3 rounded-lg border border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20 flex items-start gap-3';
                kycIcon.innerHTML = KYC_NOK_ICON;
                kycWarning.classList.remove('hidden');
                submitBtn.disabled = true;
            }

            selectedCard.classList.remove('hidden');
        }

        function clearSelection() {
            hiddenInput.value = '';
            searchInput.value = '';
            selectedCard.classList.add('hidden');
            submitBtn.disabled = false;
        }

        clearBtn.addEventListener('click', clearSelection);

        searchInput.addEventListener('input', function () {
            const q = this.value.trim();

            if (q.length < 2) {
                resultsList.classList.add('hidden');
                return;
            }

            clearTimeout(debounceTimer);
            spinner.classList.remove('hidden');

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('clients.autocomplete') }}?q=` + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(clients => {
                    spinner.classList.add('hidden');
                    resultsList.innerHTML = '';

                    if (!clients.length) {
                        resultsList.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Aucun client trouvé.</li>';
                        resultsList.classList.remove('hidden');
                        return;
                    }

                    clients.forEach(client => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3';

                        const kycBadge = client.kyc_ok
                            ? '<span class="flex-shrink-0 text-xs px-1.5 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 rounded font-medium">KYC ✓</span>'
                            : '<span class="flex-shrink-0 text-xs px-1.5 py-0.5 bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400 rounded font-medium">KYC ✗</span>';

                        li.innerHTML = `
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">${client.label}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">${client.phone ?? ''} ${client.client_id ? '· ' + client.client_id : ''}</p>
                            </div>
                            ${kycBadge}
                        `;

                        li.addEventListener('click', () => selectClient(client));
                        resultsList.appendChild(li);
                    });

                    resultsList.classList.remove('hidden');
                })
                .catch(() => spinner.classList.add('hidden'));
            }, 300);
        });

        // Fermer le dropdown si clic ailleurs
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
                resultsList.classList.add('hidden');
            }
        });

        // Bloquer soumission si pas de client sélectionné
        document.getElementById('business-form').addEventListener('submit', function (e) {
            if (!hiddenInput.value) {
                e.preventDefault();
                searchInput.focus();
                searchInput.classList.add('border-red-500');
                setTimeout(() => searchInput.classList.remove('border-red-500'), 2000);
            }
        });
    })();
    </script>
    @endpush
</x-app-layout>
