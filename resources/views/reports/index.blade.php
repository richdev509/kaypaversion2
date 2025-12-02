<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Génération de rapports
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Affichage des erreurs -->
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <strong>Erreurs de validation :</strong>
                            <ul class="list-disc list-inside mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('reports.generate') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Type de rapport -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Type de rapport
                                </label>
                                <select id="type" name="type" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="all">Tous (Dépôts + Retraits)</option>
                                    <option value="deposit">Dépôts uniquement</option>
                                    <option value="withdrawal">Retraits uniquement</option>
                                </select>
                            </div>

                            <!-- Période -->
                            <div>
                                <label for="period_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Période
                                </label>
                                <select id="period_type" name="period_type" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required onchange="toggleCustomDates()">
                                    <option value="daily">Aujourd'hui</option>
                                    <option value="weekly">Cette semaine</option>
                                    <option value="monthly">Ce mois</option>
                                    <option value="custom">Période personnalisée</option>
                                </select>
                            </div>

                            <!-- Dates personnalisées (masquées par défaut) -->
                            <div id="custom-dates" class="md:col-span-2 hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                            Date de début
                                        </label>
                                        <input type="date" id="start_date" name="start_date" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                            Date de fin
                                        </label>
                                        <input type="date" id="end_date" name="end_date" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Branche -->
                            <div class="md:col-span-2">
                                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Branche (optionnel)
                                </label>
                                <select id="branch_id" name="branch_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Toutes les branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500 mt-1">Laissez vide pour générer un rapport global</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" style="background-color: #1d4ed8; color: white;" class="font-bold py-2 px-6 rounded hover:opacity-90">
                                <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Générer le rapport
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">
                    <svg class="inline-block w-5 h-5 mr-2 -mt-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    À propos des rapports
                </h3>
                <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                    <li>Les rapports sont générés en temps réel à partir des données actuelles</li>
                    <li>Les <strong>agents</strong> peuvent uniquement générer des rapports pour leur branche</li>
                    <li>Les <strong>managers</strong> et <strong>admins</strong> peuvent générer des rapports pour toutes les branches</li>
                    <li>Les rapports incluent des statistiques détaillées et des graphiques</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomDates() {
            const periodType = document.getElementById('period_type').value;
            const customDates = document.getElementById('custom-dates');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            if (periodType === 'custom') {
                customDates.classList.remove('hidden');
                startDate.required = true;
                endDate.required = true;
            } else {
                customDates.classList.add('hidden');
                startDate.required = false;
                endDate.required = false;
            }
        }
    </script>
</x-app-layout>
