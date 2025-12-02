<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devenir Partenaire - Kaypa</title>
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
    <div class="w-full max-w-2xl">
        <div class="bg-white shadow-2xl rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-8 text-center">
                <img src="{{ asset('kaypa.png') }}" alt="Kaypa Logo" class="mx-auto h-16 mb-4">
                <h1 class="text-3xl font-bold text-white">Devenir Partenaire Kaypa</h1>
                <p class="text-purple-100 text-lg mt-2">Gagnez 25 GDS par client parrainé !</p>
            </div>

            <div class="p-8">
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="font-bold text-blue-900 mb-2">🎯 Comment ça marche ?</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>✅ Remplissez le formulaire ci-dessous</li>
                        <li>✅ Recevez un code de vérification par email</li>
                        <li>✅ Attendez l'approbation (moins de 24h)</li>
                        <li>✅ Recevez votre code de parrainage unique</li>
                        <li>✅ Gagnez 25 GDS pour chaque nouveau client inscrit avec votre code</li>
                    </ul>
                </div>

                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('affiliate.submit') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="prenom" class="block text-sm font-semibold text-gray-700 mb-2">Prénom *</label>
                            <input 
                                type="text" 
                                name="prenom" 
                                id="prenom" 
                                value="{{ old('prenom') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Jean"
                                required
                            >
                        </div>

                        <div>
                            <label for="nom" class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                            <input 
                                type="text" 
                                name="nom" 
                                id="nom" 
                                value="{{ old('nom') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Dupont"
                                required
                            >
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="votre@email.com"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Votre code de vérification sera envoyé à cette adresse</p>
                    </div>

                    <div class="mb-4">
                        <label for="telephone" class="block text-sm font-semibold text-gray-700 mb-2">Téléphone *</label>
                        <input 
                            type="tel" 
                            name="telephone" 
                            id="telephone" 
                            value="{{ old('telephone') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="+509 XXXX-XXXX"
                            required
                        >
                    </div>

                    <div class="mb-6">
                        <label for="whatsapp" class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp (optionnel)</label>
                        <input 
                            type="tel" 
                            name="whatsapp" 
                            id="whatsapp" 
                            value="{{ old('whatsapp') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="+509 XXXX-XXXX"
                        >
                        <p class="text-xs text-gray-500 mt-1">Nous vous contacterons via WhatsApp si fourni</p>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 shadow-lg"
                    >
                        Soumettre ma Demande
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('home') }}" class="text-purple-600 hover:text-purple-800 text-sm">
                        ← Retour à l'accueil
                    </a>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="bg-gray-50 px-8 py-4 border-t">
                <p class="text-xs text-gray-600 text-center">
                    En soumettant ce formulaire, vous acceptez d'être contacté par Kaypa concernant votre demande de partenariat.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
