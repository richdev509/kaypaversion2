<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe - Kaypa</title>
    <link rel="icon" type="image/png" href="{{ asset('kaypa.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center py-12">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 text-center">
                <h1 class="text-2xl font-bold text-white">Changer le mot de passe</h1>
            </div>

            <div class="p-6">
                <div class="mb-4">
                    <a href="{{ route('mobile.dashboard') }}" class="text-purple-600 hover:text-purple-800 text-sm">
                        ← Retour au tableau de bord
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('mobile.change-password') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Mot de passe actuel
                        </label>
                        <input
                            type="password"
                            name="current_password"
                            id="current_password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nouveau mot de passe
                        </label>
                        <input
                            type="password"
                            name="new_password"
                            id="new_password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 caractères</p>
                    </div>

                    <div class="mb-6">
                        <label for="new_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirmer le nouveau mot de passe
                        </label>
                        <input
                            type="password"
                            name="new_password_confirmation"
                            id="new_password_confirmation"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition"
                    >
                        Modifier le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
