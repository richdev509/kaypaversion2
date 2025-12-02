<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💰 Créer un Nouveau Compte d'Épargne
            </h2>
            <a href="{{ route('accounts.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour à la liste
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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

                    <form action="{{ route('accounts.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Section: Sélection du Client -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                👤 Client
                            </h3>

                            <div>
                                <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Sélectionner le client <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="client_id"
                                    name="client_id"
                                    required
                                    {{ $selectedClient ? 'disabled' : '' }}
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 {{ $selectedClient ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : '' }}"
                                >
                                    <option value="">-- Choisir un client --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $selectedClient->id ?? '') == $client->id ? 'selected' : '' }}>
                                            {{ $client->first_name }} {{ $client->last_name }} - {{ $client->phone }}
                                        </option>
                                    @endforeach
                                </select>

                                @if($selectedClient)
                                    <!-- Champ caché pour soumettre le client_id même si le select est disabled -->
                                    <input type="hidden" name="client_id" value="{{ $selectedClient->id }}">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        🔒 Client pré-sélectionné - Modification désactivée
                                    </p>
                                @endif

                                @error('client_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror

                                <!-- Info client sélectionné -->
                                <div id="client-info" class="hidden mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        <span class="font-medium">Client:</span> <span id="client-name"></span>
                                    </p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        <span class="font-medium">Téléphone:</span> <span id="client-phone"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Sélection du Plan -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                📅 Plan d'Épargne
                            </h3>

                            <div>
                                <label for="plan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Sélectionner le plan <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="plan_id"
                                    name="plan_id"
                                    required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">-- Choisir un plan --</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }} ({{ $plan->duree }} jours)
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror

                                <!-- Info plan sélectionné -->
                                <div id="plan-info" class="hidden mt-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        <span class="font-medium">Durée:</span> <span id="plan-duree"></span> jours
                                    </p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        <span class="font-medium">Frais d'ouverture:</span> <span id="plan-ouverture" class="font-semibold text-green-700 dark:text-green-300"></span> HTG
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        💡 Le frais d'ouverture sera égal au montant journalier sélectionné
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Montant Journalier -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                💵 Montant Journalier
                            </h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Choisir le montant à épargner par jour <span class="text-red-500">*</span>
                                </label>

                                <!-- Message d'attente -->
                                <div id="montants-loading" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Sélectionnez d'abord un plan pour voir les montants disponibles
                                    </p>
                                </div>

                                <!-- Grille des montants (cachée par défaut) -->
                                <div id="montants-grid" class="hidden grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <!-- Les montants seront chargés dynamiquement via AJAX -->
                                </div>

                                <input type="hidden" id="montant_journalier" name="montant_journalier" value="{{ old('montant_journalier') }}">

                                @error('montant_journalier')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror

                                <!-- Résumé montant sélectionné -->
                                <div id="montant-summary" class="hidden mt-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        📊 Montant sélectionné: <span id="summary-montant" class="text-indigo-700 dark:text-indigo-300 font-bold"></span> HTG/jour
                                    </p>
                                    <p class="text-sm text-gray-800 dark:text-gray-200 mt-1">
                                        💰 Total prévu: <span id="summary-total" class="font-semibold text-green-700 dark:text-green-300"></span> HTG
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Dates -->
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                📆 Dates
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="date_debut" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Date de début <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        id="date_debut"
                                        name="date_debut"
                                        value="{{ old('date_debut', date('Y-m-d')) }}"
                                        required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                    @error('date_debut')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Date de fin (calculée automatiquement)
                                    </label>
                                    <input
                                        type="text"
                                        id="date_fin_display"
                                        readonly
                                        placeholder="Sera calculée après sélection du plan"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
                                    >
                                </div>
                            </div>

                            <!-- Méthode de paiement initial -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Méthode de paiement (Frais d'ouverture) <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="payment_method"
                                    name="payment_method"
                                    required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                >
                                    <option value="">-- Sélectionner --</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>💵 Espèces (Cash)</option>
                                    <option value="moncash" {{ old('payment_method') == 'moncash' ? 'selected' : '' }}>📱 MonCash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>🏦 Virement Bancaire</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-4">
                            <a href="{{ route('accounts.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                                Annuler
                            </a>
                            <button type="submit" class="px-8 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-lg hover:shadow-xl">
                                ✅ Créer le compte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const planSelect = document.getElementById('plan_id');
        const dateDebutInput = document.getElementById('date_debut');
        const dateFinDisplay = document.getElementById('date_fin_display');
        const montantsGrid = document.getElementById('montants-grid');
        const montantsLoading = document.getElementById('montants-loading');
        const montantInput = document.getElementById('montant_journalier');
        const montantSummary = document.getElementById('montant-summary');
        const planInfo = document.getElementById('plan-info');

        let currentPlan = null;

        // ==========================================
        // 1. CHARGER LES MONTANTS QUAND UN PLAN EST SÉLECTIONNÉ
        // ==========================================
        planSelect.addEventListener('change', function() {
            const planId = this.value;

            if (!planId) {
                montantsGrid.classList.add('hidden');
                montantsLoading.classList.remove('hidden');
                montantSummary.classList.add('hidden');
                planInfo.classList.add('hidden');
                montantInput.value = '';
                dateFinDisplay.value = '';
                return;
            }

            // Charger les montants via AJAX
            fetch(`/plans/${planId}/montants`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        currentPlan = data.plan;

                        // Afficher info plan
                        document.getElementById('plan-duree').textContent = data.plan.duree;
                        document.getElementById('plan-ouverture').textContent = '(Sélectionnez un montant)';
                        planInfo.classList.remove('hidden');

                        // Calculer date de fin
                        updateDateFin();

                        // Afficher les montants
                        montantsLoading.classList.add('hidden');
                        montantsGrid.classList.remove('hidden');
                        montantsGrid.innerHTML = '';

                        data.montants.forEach(montant => {
                            const card = document.createElement('div');
                            card.className = 'montant-card p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition';
                            card.dataset.montant = montant.montant_par_jour;
                            card.dataset.total = montant.total_prevu;

                            card.innerHTML = `
                                <div class="text-center">
                                    <p class="montant-value text-lg font-bold text-gray-900 dark:text-gray-100">${new Intl.NumberFormat('fr-HT').format(montant.montant_par_jour)}</p>
                                    <p class="montant-label text-xs text-gray-500 dark:text-gray-400">HTG/jour</p>
                                    <p class="montant-total text-xs text-gray-600 dark:text-gray-300 mt-2">Total: ${new Intl.NumberFormat('fr-HT').format(montant.total_prevu)} HTG</p>
                                </div>
                            `;

                            card.addEventListener('click', function() {
                                // Retirer sélection précédente
                                document.querySelectorAll('.montant-card').forEach(c => {
                                    c.classList.remove('border-blue-600', 'dark:border-blue-400', 'bg-blue-100', 'dark:bg-blue-900/40');
                                    c.classList.add('border-gray-300', 'dark:border-gray-600');

                                    // Restaurer les couleurs de texte par défaut
                                    const value = c.querySelector('.montant-value');
                                    const label = c.querySelector('.montant-label');
                                    const total = c.querySelector('.montant-total');

                                    value.classList.remove('text-blue-900', 'dark:text-blue-100');
                                    value.classList.add('text-gray-900', 'dark:text-gray-100');

                                    label.classList.remove('text-blue-700', 'dark:text-blue-300');
                                    label.classList.add('text-gray-500', 'dark:text-gray-400');

                                    total.classList.remove('text-blue-800', 'dark:text-blue-200');
                                    total.classList.add('text-gray-600', 'dark:text-gray-300');
                                });

                                // Marquer comme sélectionné
                                this.classList.remove('border-gray-300', 'dark:border-gray-600');
                                this.classList.add('border-blue-600', 'dark:border-blue-400', 'bg-blue-100', 'dark:bg-blue-900/40');

                                // Changer les couleurs de texte pour le mode sombre
                                const value = this.querySelector('.montant-value');
                                const label = this.querySelector('.montant-label');
                                const total = this.querySelector('.montant-total');

                                value.classList.remove('text-gray-900', 'dark:text-gray-100');
                                value.classList.add('text-blue-900', 'dark:text-blue-100');

                                label.classList.remove('text-gray-500', 'dark:text-gray-400');
                                label.classList.add('text-blue-700', 'dark:text-blue-300');

                                total.classList.remove('text-gray-600', 'dark:text-gray-300');
                                total.classList.add('text-blue-800', 'dark:text-blue-200');

                                // Mettre à jour le champ caché
                                montantInput.value = this.dataset.montant;

                                // Mettre à jour le frais d'ouverture (= montant journalier sélectionné)
                                document.getElementById('plan-ouverture').textContent = new Intl.NumberFormat('fr-HT').format(this.dataset.montant);

                                // Afficher résumé
                                document.getElementById('summary-montant').textContent = new Intl.NumberFormat('fr-HT').format(this.dataset.montant);
                                document.getElementById('summary-total').textContent = new Intl.NumberFormat('fr-HT').format(this.dataset.total);
                                montantSummary.classList.remove('hidden');
                            });

                            montantsGrid.appendChild(card);
                        });
                    }
                })
                .catch(err => {
                    console.error('Erreur:', err);
                    alert('Erreur lors du chargement des montants');
                });
        });

        // ==========================================
        // 2. CALCULER DATE DE FIN
        // ==========================================
        function updateDateFin() {
            if (!currentPlan || !dateDebutInput.value) return;

            const dateDebut = new Date(dateDebutInput.value);
            const dateFin = new Date(dateDebut);
            dateFin.setDate(dateFin.getDate() + parseInt(currentPlan.duree));

            const formattedDate = dateFin.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            dateFinDisplay.value = formattedDate;
        }

        dateDebutInput.addEventListener('change', updateDateFin);

        // ==========================================
        // 3. AFFICHER INFO CLIENT SÉLECTIONNÉ
        // ==========================================
        const clientSelect = document.getElementById('client_id');
        const clientInfo = document.getElementById('client-info');

        clientSelect.addEventListener('change', function() {
            if (!this.value) {
                clientInfo.classList.add('hidden');
                return;
            }

            const selectedOption = this.options[this.selectedIndex];
            const text = selectedOption.text;
            const parts = text.split(' - ');

            document.getElementById('client-name').textContent = parts[0];
            document.getElementById('client-phone').textContent = parts[1] || '';
            clientInfo.classList.remove('hidden');
        });

        // Trigger si client pré-sélectionné
        if (clientSelect.value) {
            clientSelect.dispatchEvent(new Event('change'));
        }
    </script>
    @endpush
</x-app-layout>
