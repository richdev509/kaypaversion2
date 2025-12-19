<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💸 Nouveau Transfert
            </h2>
            <a href="{{ route('transfers.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-200">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Informations sur les limites -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 dark:bg-blue-900 dark:border-blue-700">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Informations Importantes</h3>
                        <ul class="space-y-1 text-sm text-blue-700 dark:text-blue-300">
                            <li>• Montant minimum: <strong>{{ number_format($settings->min_amount, 0) }} GDS</strong></li>
                            <li>• Montant maximum: <strong>{{ number_format($settings->max_amount, 0) }} GDS</strong></li>
                            <li>• Réduction de <strong>{{ $settings->kaypa_client_discount }}%</strong> pour les clients Kaypa</li>
                            <li>• Format téléphone: <strong>+509 XX-XX-XXXX</strong></li>
                            <li>• NINU: <strong>10 chiffres</strong></li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('transfers.store') }}" id="transferForm">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Informations de l'expéditeur -->
                    <div>
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                <h3 class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    Expéditeur
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <!-- Compte Kaypa -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Numéro de Compte Kaypa (optionnel)
                                    </label>
                                    <div class="flex gap-2">
                                        <input type="text" name="sender_account_id" id="sender_account_id"
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sender_account_id') border-red-500 @enderror"
                                            value="{{ old('sender_account_id') }}"
                                            placeholder="KYP-XXXXXX">
                                        <button type="button" id="checkAccountBtn"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Réduction de {{ $settings->kaypa_client_discount }}% si compte Kaypa
                                    </p>
                                    <div id="accountInfo" class="mt-2 hidden">
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 dark:bg-green-900 dark:border-green-700">
                                            <div class="flex items-center text-green-700 dark:text-green-300">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span><strong id="clientName"></strong> - <span id="clientPhone"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('sender_account_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Nom Complet -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nom Complet <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="sender_name" id="sender_name" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sender_name') border-red-500 @enderror"
                                        value="{{ old('sender_name') }}">
                                    @error('sender_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Téléphone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Téléphone <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <select name="sender_country_code" id="sender_country_code"
                                            class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="+509" selected>🇭🇹 +509</option>
                                            <option value="+1">🇺🇸 +1</option>
                                            <option value="+1">🇨🇦 +1</option>
                                            <option value="+33">🇫🇷 +33</option>
                                            <option value="+590">🇬🇵 +590</option>
                                            <option value="+596">🇲🇶 +596</option>
                                            <option value="+594">🇬🇫 +594</option>
                                            <option value="+1-809">🇩🇴 +1-809</option>
                                        </select>
                                        <input type="text" name="sender_phone" id="sender_phone" required
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sender_phone') border-red-500 @enderror"
                                            value="{{ old('sender_phone') }}"
                                            placeholder="XX-XX-XXXX">
                                    </div>
                                    @error('sender_phone')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                    @error('sender_country_code')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- NINU -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        NINU <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="sender_ninu" required maxlength="10"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sender_ninu') border-red-500 @enderror"
                                        value="{{ old('sender_ninu') }}"
                                        placeholder="10 chiffres">
                                    @error('sender_ninu')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Adresse (Optionnel) -->
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Adresse (Optionnel)</h4>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Adresse
                                            </label>
                                            <input type="text" name="sender_address"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                value="{{ old('sender_address') }}">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Département
                                            </label>
                                            <select name="sender_department_id" id="sender_department"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Sélectionner...</option>
                                                @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}" {{ old('sender_department_id') == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Commune
                                            </label>
                                            <select name="sender_commune_id" id="sender_commune" disabled
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Sélectionner d'abord le département...</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Ville/Quartier
                                            </label>
                                            <select name="sender_city_id" id="sender_city" disabled
                                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Sélectionner d'abord la commune...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bénéficiaire et Montant -->
                    <div class="space-y-6">
                        <!-- Informations du bénéficiaire -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4 bg-gradient-to-r from-green-600 to-green-700 text-white">
                                <h3 class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    Bénéficiaire
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nom Complet <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="receiver_name" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('receiver_name') border-red-500 @enderror"
                                        value="{{ old('receiver_name') }}">
                                    @error('receiver_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Téléphone <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <select name="receiver_country_code" id="receiver_country_code"
                                            class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="+509" selected>🇭🇹 +509</option>
                                            <option value="+1">🇺🇸 +1</option>
                                            <option value="+1">🇨🇦 +1</option>
                                            <option value="+33">🇫🇷 +33</option>
                                            <option value="+590">🇬🇵 +590</option>
                                            <option value="+596">🇲🇶 +596</option>
                                            <option value="+594">🇬🇫 +594</option>
                                            <option value="+1-809">🇩🇴 +1-809</option>
                                        </select>
                                        <input type="text" name="receiver_phone" required
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('receiver_phone') border-red-500 @enderror"
                                            value="{{ old('receiver_phone') }}"
                                            placeholder="XX-XX-XXXX">
                                    </div>
                                    @error('receiver_phone')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                    @error('receiver_country_code')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Montant et Frais -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white">
                                <h3 class="font-semibold text-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                                    </svg>
                                    Montant
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Montant à Transférer <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="amount" id="amount" required
                                        min="{{ $settings->min_amount }}"
                                        max="{{ $settings->max_amount }}"
                                        step="50"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('amount') border-red-500 @enderror"
                                        value="{{ old('amount') }}">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Entre {{ number_format($settings->min_amount, 0) }} et {{ number_format($settings->max_amount, 0) }} GDS
                                    </p>
                                    @error('amount')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Calcul des frais -->
                                <div id="feesCalculation" class="hidden">
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Montant:</span>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100"><span id="displayAmount">0</span> GDS</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Frais:</span>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100"><span id="displayFees">0</span> GDS</span>
                                        </div>
                                        <div id="discountRow" class="flex justify-between text-sm hidden">
                                            <span class="text-green-600 dark:text-green-400">Réduction Kaypa:</span>
                                            <span class="font-semibold text-green-600 dark:text-green-400">-<span id="displayDiscount">0</span> GDS</span>
                                        </div>
                                        <div class="flex justify-between text-base pt-2 border-t border-gray-200 dark:border-gray-600">
                                            <span class="font-bold text-gray-900 dark:text-gray-100">TOTAL À PAYER:</span>
                                            <span class="font-bold text-blue-600 dark:text-blue-400"><span id="displayTotal">0</span> GDS</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Note (optionnel)
                                    </label>
                                    <textarea name="note" rows="2"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('transfers.index') }}"
                        class="px-6 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                        </svg>
                        Créer le Transfert
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    // Vérifier le compte Kaypa
    let hasKaypaAccount = false;

    document.getElementById('checkAccountBtn').addEventListener('click', function() {
        const accountId = document.getElementById('sender_account_id').value;

        if (!accountId) {
            alert('Veuillez entrer un numéro de compte');
            return;
        }

        fetch(`/transfers/check/account?account_id=${accountId}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    document.getElementById('accountInfo').classList.remove('hidden');
                    document.getElementById('clientName').textContent = data.client_name;
                    document.getElementById('clientPhone').textContent = data.phone;
                    document.getElementById('sender_name').value = data.client_name;
                    document.getElementById('sender_phone').value = data.phone;
                    hasKaypaAccount = true;
                    calculateFees();
                } else {
                    alert('Compte non trouvé ou inactif');
                    document.getElementById('accountInfo').classList.add('hidden');
                    hasKaypaAccount = false;
                }
            });
    });

    // Calculer les frais
    document.getElementById('amount').addEventListener('input', calculateFees);
    document.getElementById('sender_account_id').addEventListener('change', function() {
        if (!this.value) {
            hasKaypaAccount = false;
            document.getElementById('accountInfo').classList.add('hidden');
            calculateFees();
        }
    });

    function calculateFees() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;

        if (amount < {{ $settings->min_amount }} || amount > {{ $settings->max_amount }}) {
            document.getElementById('feesCalculation').classList.add('hidden');
            return;
        }

        fetch('/transfers/calculate/fees', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                amount: amount,
                has_account: hasKaypaAccount
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('displayAmount').textContent = data.amount.toLocaleString();
            document.getElementById('displayFees').textContent = data.fees.toLocaleString();
            document.getElementById('displayTotal').textContent = data.total.toLocaleString();

            if (data.discount > 0) {
                document.getElementById('displayDiscount').textContent = data.discount.toLocaleString();
                document.getElementById('discountRow').classList.remove('hidden');
            } else {
                document.getElementById('discountRow').classList.add('hidden');
            }

            document.getElementById('feesCalculation').classList.remove('hidden');
        });
    }

    // Cascade géographique - Expéditeur
    document.getElementById('sender_department').addEventListener('change', function() {
        const departmentId = this.value;
        const communeSelect = document.getElementById('sender_commune');
        const citySelect = document.getElementById('sender_city');

        communeSelect.innerHTML = '<option value="">Chargement...</option>';
        communeSelect.disabled = true;
        citySelect.innerHTML = '<option value="">Sélectionner d\'abord la commune...</option>';
        citySelect.disabled = true;

        if (departmentId) {
            fetch(`/get-communes/${departmentId}`)
                .then(response => response.json())
                .then(data => {
                    communeSelect.innerHTML = '<option value="">Sélectionner...</option>';
                    data.forEach(commune => {
                        communeSelect.innerHTML += `<option value="${commune.id}">${commune.name}</option>`;
                    });
                    communeSelect.disabled = false;
                });
        }
    });

    document.getElementById('sender_commune').addEventListener('change', function() {
        const communeId = this.value;
        const citySelect = document.getElementById('sender_city');

        citySelect.innerHTML = '<option value="">Chargement...</option>';
        citySelect.disabled = true;

        if (communeId) {
            fetch(`/get-cities/${communeId}`)
                .then(response => response.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">Sélectionner...</option>';
                    data.forEach(city => {
                        citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                    });
                    citySelect.disabled = false;
                });
        }
    });
    </script>
    @endpush
</x-app-layout>
