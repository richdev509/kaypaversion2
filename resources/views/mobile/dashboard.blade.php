<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tableau de Bord - Kaypa</title>
    <link rel="icon" type="image/png" href="{{ asset('kaypa.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="min-h-screen pb-12">
        <!-- Header -->
        <header class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <img src="{{ asset('kaypa.png') }}" alt="Kaypa" class="h-10 mr-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">Bonjour, {{ $client->full_name }}</h1>
                            <p class="text-sm text-gray-600">Client ID: {{ $client->client_id }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('mobile.logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if($client->password_reset)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg">
                    <strong>⚠️ Mot de passe temporaire:</strong> Il est recommandé de changer votre mot de passe.
                    <a href="{{ route('mobile.change-password') }}" class="underline font-semibold">Changer maintenant</a>
                </div>
            @endif

            <!-- Client Info Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Mes Informations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nom Complet</p>
                        <p class="font-semibold text-gray-900">{{ $client->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-semibold text-gray-900">{{ $client->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Téléphone</p>
                        <p class="font-semibold text-gray-900">{{ $client->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Agence</p>
                        <p class="font-semibold text-gray-900">{{ $client->branch->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Accounts -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Mes Comptes</h2>

                @if($client->accounts->isEmpty())
                    <p class="text-gray-600">Aucun compte enregistré.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($client->accounts as $account)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-sm text-gray-600">Plan</p>
                                        <p class="font-bold text-lg text-gray-900">{{ $account->plan->name ?? 'N/A' }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $account->status === 'active' ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">N° de Compte</p>
                                    <p class="font-semibold text-gray-900">{{ $account->account_number }}</p>
                                </div>

                                <div class="mt-3">
                                    <p class="text-sm text-gray-600">Solde</p>
                                    <p class="text-2xl font-bold text-purple-600">
                                        {{ number_format($account->balance, 0, ',', ' ') }} FCFA
                                    </p>
                                </div>

                                <div class="mt-3 pt-3 border-t">
                                    <p class="text-xs text-gray-500">Ouvert le {{ $account->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('mobile.change-password') }}" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <h3 class="font-bold text-gray-900">Changer le mot de passe</h3>
                </a>

                <div class="bg-white rounded-lg shadow-lg p-6 text-center opacity-50">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="font-bold text-gray-900">Historique des transactions</h3>
                    <p class="text-xs text-gray-500 mt-1">Bientôt disponible</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 text-center opacity-50">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="font-bold text-gray-900">Demander un retrait</h3>
                    <p class="text-xs text-gray-500 mt-1">Bientôt disponible</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
