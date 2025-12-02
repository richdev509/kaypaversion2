<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Rôles et Permissions
            </h2>
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour aux utilisateurs
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Introduction -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
                            Système de Gestion des Rôles et Permissions
                        </h3>
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            Ce système définit les capacités de chaque rôle dans l'application KAYPA. Les permissions sont automatiquement appliquées en fonction du rôle attribué à chaque utilisateur.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Liste des Rôles et Permissions -->
            @foreach($roles as $role)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <span class="px-4 py-2 rounded-full text-sm font-semibold
                                    @if($role->name === 'admin') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                    @elseif($role->name === 'manager') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                    @elseif($role->name === 'agent') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                    @elseif($role->name === 'comptable') bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400
                                    @elseif($role->name === 'viewer') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                                    @endif">
                                    {{ ucfirst($role->name) }}
                                </span>
                                <span class="ml-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $role->permissions->count() }} permission(s)
                                </span>
                            </div>
                        </div>

                        @if($role->permissions->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($role->permissions as $permission)
                                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $permission->name }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                                Aucune permission définie pour ce rôle.
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Légende des Permissions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        📖 Légende des Permissions
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>users.*</strong> - Gestion des utilisateurs
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Créer, voir, modifier et supprimer des utilisateurs
                            </p>
                        </div>

                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>branches.*</strong> - Gestion des branches
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Créer, voir, modifier et supprimer des agences
                            </p>
                        </div>

                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>clients.*</strong> - Gestion des clients
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Créer, voir, modifier et supprimer des clients
                            </p>
                        </div>

                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>accounts.*</strong> - Gestion des comptes
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Créer, voir, modifier et supprimer des comptes épargne
                            </p>
                        </div>

                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>payments.create</strong> - Effectuer dépôts
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Enregistrer des paiements pour les comptes
                            </p>
                        </div>

                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>withdrawals.create</strong> - Effectuer retraits
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Traiter les demandes de retrait
                            </p>
                        </div>

                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>reports.view</strong> - Voir les rapports
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Accéder aux rapports et statistiques
                            </p>
                        </div>

                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <strong>settings.manage</strong> - Gérer paramètres
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Configuration globale du système
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
