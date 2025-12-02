<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ➕ Nouveau mouvement de fonds
            </h2>
            <a href="{{ route('fund-movements.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('fund-movements.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Type de mouvement -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Type de mouvement <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition"
                                           :class="{'border-blue-600 bg-blue-50 dark:bg-gray-700': document.querySelector('input[name=type]:checked')?.value === 'IN'}">
                                        <input type="radio" name="type" value="IN" required class="mr-2" onchange="updateFormFields()">
                                        <div>
                                            <div class="font-semibold text-sm text-gray-900 dark:text-gray-100">Entrée (IN)</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Réception de fonds</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:bg-orange-50 dark:hover:bg-gray-700 transition"
                                           :class="{'border-orange-600 bg-orange-50 dark:bg-gray-700': document.querySelector('input[name=type]:checked')?.value === 'OUT'}">
                                        <input type="radio" name="type" value="OUT" required class="mr-2" onchange="updateFormFields()">
                                        <div>
                                            <div class="font-semibold text-sm text-gray-900 dark:text-gray-100">Sortie (OUT)</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Envoi de fonds</div>
                                        </div>
                                    </label>
                                </div>
                                @error('type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Montant -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Montant (HTG) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="amount" id="amount" step="0.01" min="1" required
                                       value="{{ old('amount') }}"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type de source -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Type de source <span class="text-red-500">*</span>
                                </label>
                                <select name="source_type" id="source_type" required onchange="updateSourceFields()"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="SUCCURSALE" {{ old('source_type') === 'SUCCURSALE' ? 'selected' : '' }}>Succursale</option>
                                    <option value="BANQUE" {{ old('source_type') === 'BANQUE' ? 'selected' : '' }}>Banque</option>
                                    <option value="EXTERNE" {{ old('source_type') === 'EXTERNE' ? 'selected' : '' }}>Source externe</option>
                                    <option value="INITIAL" {{ old('source_type') === 'INITIAL' ? 'selected' : '' }}>Capital initial</option>
                                </select>
                                @error('source_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Branche source (si OUT ou SUCCURSALE) -->
                            <div id="source_branch_field" style="display: none;">
                                <label for="source_branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Branche source
                                </label>
                                <select name="source_branch_id" id="source_branch_id"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('source_branch_id', $userBranch) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('source_branch_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Source externe (si BANQUE ou EXTERNE) -->
                            <div id="external_source_field" style="display: none;">
                                <label for="external_source" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nom de la source externe
                                </label>
                                <input type="text" name="external_source" id="external_source" value="{{ old('external_source') }}"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                       placeholder="Ex: Unibank, Sogebank, Partenaire X...">
                                @error('external_source')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Branche destination -->
                            <div>
                                <label for="destination_branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Branche destination <span class="text-red-500">*</span>
                                </label>
                                <select name="destination_branch_id" id="destination_branch_id" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('destination_branch_id', $userBranch) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_branch_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Raison -->
                            <div class="md:col-span-2">
                                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Raison du mouvement <span class="text-red-500">*</span>
                                </label>
                                <textarea name="reason" id="reason" rows="2" required
                                          class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                          placeholder="Décrivez la raison de ce mouvement de fonds...">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes additionnelles -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Notes additionnelles (optionnel)
                                </label>
                                <textarea name="notes" id="notes" rows="2"
                                          class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                          placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex justify-end space-x-3 mt-6">
                            <a href="{{ route('fund-movements.index') }}"
                               class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition">
                                Annuler
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                💾 Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateSourceFields() {
            const sourceType = document.getElementById('source_type').value;
            const sourceBranchField = document.getElementById('source_branch_field');
            const externalSourceField = document.getElementById('external_source_field');

            if (sourceType === 'SUCCURSALE') {
                sourceBranchField.style.display = 'block';
                externalSourceField.style.display = 'none';
            } else if (sourceType === 'BANQUE' || sourceType === 'EXTERNE') {
                sourceBranchField.style.display = 'none';
                externalSourceField.style.display = 'block';
            } else {
                sourceBranchField.style.display = 'none';
                externalSourceField.style.display = 'none';
            }
        }

        function updateFormFields() {
            updateSourceFields();
        }

        // Initialiser à l'ouverture de la page
        document.addEventListener('DOMContentLoaded', function() {
            updateSourceFields();
        });
    </script>
</x-app-layout>
