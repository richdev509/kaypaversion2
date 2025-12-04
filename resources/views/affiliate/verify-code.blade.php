<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Email - Kaypa</title>
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
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-8 text-center">
                <img src="{{ asset('kaypa.png') }}" alt="Kaypa Logo" class="mx-auto h-16 mb-4">
                <h1 class="text-2xl font-bold text-white">Vérification de l'Email</h1>
                <p class="text-purple-100 text-sm mt-2">Entrez le code à 4 chiffres</p>
            </div>

            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <p class="text-sm text-blue-800">
                        <strong>📧 Email envoyé à :</strong><br>
                        {{ $affiliate->email }}
                    </p>
                    <p class="text-xs text-blue-600 mt-2">
                        Vérifiez votre boîte de réception et vos spams
                    </p>
                </div>

                <form method="POST" action="{{ route('affiliate.verify', $affiliate->id) }}">
                    @csrf

                    <div class="mb-6">
                        <label for="code" class="block text-sm font-semibold text-gray-700 mb-2 text-center">
                            Code de Vérification
                        </label>
                        <input
                            type="text"
                            name="code"
                            id="code"
                            maxlength="4"
                            class="w-full px-4 py-4 text-center text-2xl font-bold tracking-widest border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="0000"
                            required
                            autofocus
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg"
                    >
                        Vérifier
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('affiliate.resend-code', $affiliate->id) }}">
                        @csrf
                        <button type="submit" class="text-purple-600 hover:text-purple-800 text-sm underline">
                            Renvoyer le code
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
