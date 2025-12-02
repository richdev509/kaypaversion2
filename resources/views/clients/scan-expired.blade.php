<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Expiré - KAYPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-600 to-orange-700 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-8 text-center">
            <!-- Icon Error -->
            <div class="w-24 h-24 bg-red-100 rounded-full mx-auto mb-6 flex items-center justify-center">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                QR Code Expiré
            </h1>

            <!-- Description -->
            <p class="text-gray-600 mb-6 leading-relaxed">
                Ce QR Code a expiré après 3 minutes d'inactivité.
                Veuillez générer un nouveau code depuis le formulaire de création de client.
            </p>

            <!-- Info Box -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-left">
                        <h3 class="font-semibold text-orange-900 text-sm">Pourquoi ce délai?</h3>
                        <p class="text-xs text-orange-700 mt-1">
                            Pour votre sécurité, les QR Codes expirent automatiquement après 3 minutes pour éviter toute utilisation malveillante.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Comment procéder?
                </h3>
                <ol class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2">
                        <span class="font-bold text-blue-600">1.</span>
                        <span>Retournez sur l'ordinateur</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold text-blue-600">2.</span>
                        <span>Rechargez la page de création de client</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold text-blue-600">3.</span>
                        <span>Un nouveau QR Code sera généré automatiquement</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold text-blue-600">4.</span>
                        <span>Scannez-le immédiatement pour prendre les photos</span>
                    </li>
                </ol>
            </div>

            <!-- Action Button -->
            <button
                onclick="window.close()"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl transition-colors shadow-lg hover:shadow-xl"
            >
                Fermer cette page
            </button>

            <p class="text-xs text-gray-500 mt-4">
                Besoin d'aide? Contactez le support KAYPA
            </p>
        </div>

        <!-- Footer Wave -->
        <div class="h-2 bg-gradient-to-r from-red-500 via-orange-500 to-red-500"></div>
    </div>
</body>
</html>
