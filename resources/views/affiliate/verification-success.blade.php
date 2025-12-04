<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande Envoyée - Kaypa</title>
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
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Félicitations !</h1>
                <p class="text-green-100 text-sm mt-2">Email vérifié avec succès</p>
            </div>

            <div class="p-8">
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <h3 class="font-bold text-green-900 mb-2">✅ Demande Envoyée</h3>
                    <p class="text-sm text-green-800">
                        Votre demande de partenariat a été soumise avec succès !
                    </p>
                </div>

                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="font-bold text-blue-900 mb-2">📧 Prochaines Étapes</h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li>✓ Notre équipe va examiner votre demande</li>
                        <li>✓ Vous recevrez une réponse dans les <strong>24 heures</strong></li>
                        <li>✓ Vérifiez votre email : <strong>{{ $affiliate->email }}</strong></li>
                        <li>✓ Nous vous contacterons aussi sur WhatsApp : <strong>{{ $affiliate->whatsapp ?? $affiliate->telephone }}</strong></li>
                    </ul>
                </div>

                <div class="mb-6 bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                    <h3 class="font-bold text-purple-900 mb-2">💰 Après Approbation</h3>
                    <p class="text-sm text-purple-800">
                        Vous recevrez votre <strong>code de parrainage unique</strong> par email.<br>
                        Gagnez <strong class="text-lg">25 GDS</strong> pour chaque nouveau client que vous amenez !
                    </p>
                </div>

                <a
                    href="{{ route('home') }}"
                    class="block w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition text-center"
                >
                    Retour à l'Accueil
                </a>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t text-center">
                <p class="text-xs text-gray-600">
                    Merci de votre intérêt pour le programme de partenariat Kaypa !
                </p>
            </div>
        </div>
    </div>
</body>
</html>
