<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Gestion d\'Accès Client') }}
            </h2>
            <a href="{{ route('client-access.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-6">Informations du Client</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Client ID</label>
                            <p class="mt-1 text-lg font-semibold">{{ $client->client_id ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nom Complet</label>
                            <p class="mt-1 text-lg font-semibold">{{ $client->full_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</label>
                            <p class="mt-1 text-lg">{{ $client->phone ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                            @if($client->email)
                                <p class="mt-1 text-lg">
                                    <span class="inline-flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                        </svg>
                                        {{ $client->email }}
                                    </span>
                                </p>
                            @else
                                <p class="mt-1 text-lg text-red-500">
                                    <span class="inline-flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Aucun email enregistré
                                    </span>
                                </p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Statut d'Accès</label>
                            <p class="mt-1">
                                @if($client->password)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Accès Accordé
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Aucun Accès
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Branche</label>
                            <p class="mt-1 text-lg">{{ $client->branch->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        @if($client->email)
                            <div class="flex gap-4">
                                @if(!$client->password)
                                    <form method="POST" action="{{ route('client-access.grant', $client) }}" onsubmit="return confirm('Voulez-vous vraiment accorder l\'accès à ce client ? Un email sera envoyé avec les identifiants.')">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition inline-flex items-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            Accorder l'Accès
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('client-access.grant', $client) }}" onsubmit="return confirm('Un nouveau mot de passe sera généré et envoyé au client.')">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition inline-flex items-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Regénérer le Mot de Passe
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('client-access.revoke', $client) }}" onsubmit="return confirm('Voulez-vous vraiment révoquer l\'accès de ce client ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition inline-flex items-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            Révoquer l'Accès
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2">ℹ️ Information</h4>
                                <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                    <li>• Un mot de passe aléatoire sécurisé sera généré automatiquement</li>
                                    <li>• Le client recevra le mot de passe par email à : <strong>{{ $client->email }}</strong></li>
                                    <li>• Le client pourra se connecter sur : <strong>https://mykaypa.com/mobile/login</strong></li>
                                    <li>• Login avec son <strong>Client ID: {{ $client->client_id }}</strong> et le mot de passe reçu</li>
                                </ul>
                            </div>
                        @else
                            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                <h4 class="font-semibold text-red-900 dark:text-red-300 mb-2">⚠️ Email Manquant</h4>
                                <p class="text-sm text-red-800 dark:text-red-200">
                                    Ce client n'a pas d'adresse email enregistrée. Veuillez d'abord ajouter une adresse email valide dans les informations du client pour pouvoir accorder l'accès.
                                </p>
                                <a href="{{ route('clients.edit', $client) }}" class="mt-3 inline-block px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md">
                                    Modifier le Client
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
