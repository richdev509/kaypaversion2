<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Connexion Client - Kaypa</title>
    <link rel="icon" type="image/png" href="{{ asset('kaypa.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-8 text-center">
                <img src="{{ asset('kaypa.png') }}" alt="Kaypa Logo" class="mx-auto h-16 mb-4">
                <h1 class="text-2xl font-bold text-white">Kaypa Mobile</h1>
                <p class="text-purple-100 text-sm mt-2">Accès Client</p>
            </div>

            <!-- Messages -->
            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('mobile.login') }}">
                    @csrf

                    <!-- Client ID -->
                    <div class="mb-6">
                        <label for="client_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Identifiant Client
                        </label>
                        <input
                            type="text"
                            name="client_id"
                            id="client_id"
                            value="{{ old('client_id') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Ex: KYP001"
                            required
                            autofocus
                        >
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Mot de passe
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 shadow-lg"
                    >
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Se Connecter
                    </button>
                </form>

                <!-- Help Text -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Vous n'avez pas reçu vos identifiants ?
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        Contactez votre agence Kaypa
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 text-center border-t">
                <p class="text-xs text-gray-500">
                    © 2024 Kaypa. Tous droits réservés.
                </p>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-white bg-opacity-20 backdrop-blur-lg rounded-lg p-4 text-white">
            <h3 class="font-semibold mb-2">ℹ️ Première connexion ?</h3>
            <ul class="text-sm space-y-1">
                <li>• Utilisez l'identifiant client reçu par email</li>
                <li>• Entrez le mot de passe temporaire</li>
                <li>• Vous pourrez le changer après connexion</li>
            </ul>
        </div>
    </div>
</body>
</html>
