<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💰 Payer le Transfert
            </h2>
            <a href="{{ route('transfers.show', $transfer) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('error'))
        <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
        @endif

        <!-- Informations du transfert -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">
                <i class="fas fa-info-circle"></i> Informations du Transfert
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Numéro</p>
                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $transfer->transfer_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Expéditeur</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $transfer->sender_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Bénéficiaire</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $transfer->receiver_name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Montant à payer</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($transfer->amount, 0) }} GDS</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('transfers.pay.process', $transfer) }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Vérification du bénéficiaire -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-500 to-green-600">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-user-check"></i> Vérification du Bénéficiaire
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Important:</strong> Vous devez vérifier l'identité du bénéficiaire avant de procéder au paiement.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom du Bénéficiaire (référence)</label>
                            <input type="text" value="{{ $transfer->receiver_name }}" readonly
                                class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-semibold">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone du Bénéficiaire (référence)</label>
                            <input type="text" value="{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}" readonly
                                class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-semibold">
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

                        <h4 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Vérification Obligatoire</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                NINU du Bénéficiaire <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="receiver_ninu" placeholder="10 chiffres" maxlength="10" required autofocus
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white @error('receiver_ninu') border-red-500 @enderror">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Demandez le NINU au bénéficiaire et vérifiez son document d'identité</p>
                            @error('receiver_ninu')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Téléphone (Vérification) <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <select name="receiver_country_code_verify" required
                                    class="w-32 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                                    <option value="+509" {{ $transfer->receiver_country_code == '+509' ? 'selected' : '' }}>🇭🇹 +509</option>
                                    <option value="+1" {{ $transfer->receiver_country_code == '+1' ? 'selected' : '' }}>🇺🇸🇨🇦 +1</option>
                                    <option value="+33" {{ $transfer->receiver_country_code == '+33' ? 'selected' : '' }}>🇫🇷 +33</option>
                                    <option value="+590" {{ $transfer->receiver_country_code == '+590' ? 'selected' : '' }}>🇬🇵 +590</option>
                                    <option value="+596" {{ $transfer->receiver_country_code == '+596' ? 'selected' : '' }}>🇲🇶 +596</option>
                                    <option value="+594" {{ $transfer->receiver_country_code == '+594' ? 'selected' : '' }}>🇬🇫 +594</option>
                                    <option value="+1-809" {{ $transfer->receiver_country_code == '+1-809' ? 'selected' : '' }}>🇩🇴 +1-809</option>
                                </select>
                                <input type="text" name="receiver_phone_verify" placeholder="XX-XX-XXXX" required
                                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white @error('receiver_phone_verify') border-red-500 @enderror">
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Doit correspondre au téléphone du bénéficiaire</p>
                            @error('receiver_phone_verify')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

                        <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300 mb-4">Adresse du Bénéficiaire (Optionnel)</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adresse</label>
                            <input type="text" name="receiver_address" placeholder="Adresse complète"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Département</label>
                            <select name="receiver_department_id" id="receiver_department"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner...</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Commune</label>
                            <select name="receiver_commune_id" id="receiver_commune" disabled
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white disabled:opacity-50">
                                <option value="">Sélectionner d'abord le département...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ville/Quartier</label>
                            <select name="receiver_city_id" id="receiver_city" disabled
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white disabled:opacity-50">
                                <option value="">Sélectionner d'abord la commune...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Récapitulatif du paiement -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-500 to-blue-600">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-file-invoice-dollar"></i> Récapitulatif du Paiement
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Informations du transfert -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Numéro de Transfert:</span>
                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ $transfer->transfer_number }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Date d'envoi:</span>
                                <span class="text-gray-900 dark:text-white">{{ $transfer->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Branche d'envoi:</span>
                                <span class="text-gray-900 dark:text-white">{{ $transfer->branch->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Envoyé par:</span>
                                <span class="text-gray-900 dark:text-white">{{ $transfer->createdBy->name }}</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700"></div>

                        <!-- Montant du transfert -->
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">Montant du Transfert:</span>
                                <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($transfer->amount, 0) }} GDS</span>
                            </div>
                        </div>

                        <!-- Détails des frais -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
                            <h4 class="text-blue-600 dark:text-blue-400 font-semibold mb-3">
                                <i class="fas fa-chart-bar"></i> Détails des Frais
                            </h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Montant initial:</span>
                                    <span class="text-gray-900 dark:text-white">{{ number_format($transfer->amount, 0) }} GDS</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Frais de transfert:</span>
                                    <span class="text-gray-900 dark:text-white">{{ number_format($transfer->fees, 0) }} GDS</span>
                                </div>
                                @if($transfer->discount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Réduction Kaypa:</span>
                                    <span class="text-green-600 dark:text-green-400 font-semibold">-{{ number_format($transfer->discount, 0) }} GDS</span>
                                </div>
                                @endif
                                <div class="border-t border-gray-300 dark:border-gray-600 pt-2">
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-900 dark:text-white">Total payé par l'expéditeur:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($transfer->total_amount, 0) }} GDS</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <i class="fas fa-info-circle"></i> L'expéditeur a payé les frais de transfert
                            </p>
                        </div>

                        <!-- Montant à remettre -->
                        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-center">
                            <p class="text-white text-sm mb-2">
                                <i class="fas fa-hand-holding-usd"></i> Montant à Remettre au Bénéficiaire
                            </p>
                            <p class="text-4xl font-bold text-white">
                                {{ number_format($transfer->amount, 0) }} GDS
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Confirmation -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-2 border-red-500 dark:border-red-600">
                    <div class="p-6 space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" id="confirmIdentity" required
                                class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                <strong>J'ai vérifié l'identité du bénéficiaire</strong> (NINU + document d'identité)
                            </span>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" id="confirmAmount" required
                                class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                <strong>Je remets {{ number_format($transfer->amount, 0) }} GDS</strong> au bénéficiaire
                            </span>
                        </label>

                        <div class="space-y-3 pt-4">
                            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i>
                                Confirmer le Paiement
                            </button>
                            <a href="{{ route('transfers.show', $transfer) }}"
                                class="w-full bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                                <i class="fas fa-times"></i>
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Cascade géographique
document.getElementById('receiver_department').addEventListener('change', function() {
    const departmentId = this.value;
    const communeSelect = document.getElementById('receiver_commune');
    const citySelect = document.getElementById('receiver_city');

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

document.getElementById('receiver_commune').addEventListener('change', function() {
    const communeId = this.value;
    const citySelect = document.getElementById('receiver_city');

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

// Validation du formulaire
document.querySelector('form').addEventListener('submit', function(e) {
    if (!document.getElementById('confirmIdentity').checked || !document.getElementById('confirmAmount').checked) {
        e.preventDefault();
        alert('Veuillez cocher toutes les cases de confirmation');
        return false;
    }
});
</script>
@endpush
    </div>
</div>
</x-app-layout>
