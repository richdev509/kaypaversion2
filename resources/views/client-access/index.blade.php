<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestion d\'Accès Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
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

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Rechercher un client</h3>
                        <form method="POST" action="{{ route('client-access.search') }}" class="flex gap-4">
                            @csrf
                            <div class="flex-1">
                                <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Client ID ou Numéro de Carnet
                                </label>
                                <input
                                    type="text"
                                    name="client_id"
                                    id="client_id"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                    placeholder="Ex: KYP001 ou numéro de compte"
                                    required
                                >
                            </div>
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
                                >
                                    Rechercher
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-8 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h4 class="font-semibold mb-2">📝 Instructions</h4>
                        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>Entrez le Client ID ou le numéro de carnet du client</li>
                            <li>Le système affichera les informations du client</li>
                            <li>Si le client a un email, vous pourrez accorder l'accès</li>
                            <li>Un mot de passe sera généré automatiquement et envoyé par email</li>
                            <li>Le client pourra se connecter sur <strong class="text-blue-600">https://mykaypa.com/mobile/login</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
