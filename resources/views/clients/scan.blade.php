<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scanner les Documents - KAYPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-600 to-indigo-700 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-t-2xl p-6 text-center shadow-xl">
                <div class="w-20 h-20 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Scanner les Documents</h1>
                <p class="text-sm text-gray-600">Prenez 3 photos pour compléter l'inscription</p>
            </div>

            <!-- Progress Bar -->
            <div class="bg-white px-6 py-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600">Progression</span>
                    <span class="text-xs font-medium text-blue-600"><span id="progress-count">0</span>/3</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <!-- Photo Cards -->
            <div class="bg-white px-6 py-4 space-y-4">
                <!-- Photo 1: Pièce d'identité (Recto) -->
                <div id="card-front" class="border-2 border-gray-200 rounded-xl p-4 transition-all hover:border-blue-300">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold">1</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Pièce d'identité (Recto)</h3>
                                <p class="text-xs text-gray-500">Face avant du document</p>
                            </div>
                        </div>
                        <svg id="check-front" class="w-6 h-6 text-green-500 hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <input
                        type="file"
                        id="input-front"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                    >
                    <button
                        onclick="document.getElementById('input-front').click()"
                        id="btn-front"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Prendre la photo
                    </button>
                    <div id="preview-front" class="hidden mt-3">
                        <img id="img-front" src="" class="w-full h-32 object-cover rounded-lg">
                    </div>
                </div>

                <!-- Photo 2: Pièce d'identité (Verso) -->
                <div id="card-back" class="border-2 border-gray-200 rounded-xl p-4 transition-all hover:border-blue-300 opacity-50">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold">2</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Pièce d'identité (Verso)</h3>
                                <p class="text-xs text-gray-500">Face arrière du document</p>
                            </div>
                        </div>
                        <svg id="check-back" class="w-6 h-6 text-green-500 hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <input
                        type="file"
                        id="input-back"
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        disabled
                    >
                    <button
                        onclick="document.getElementById('input-back').click()"
                        id="btn-back"
                        disabled
                        class="w-full bg-gray-300 text-gray-500 font-medium py-3 rounded-lg transition flex items-center justify-center gap-2 cursor-not-allowed"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Prendre la photo
                    </button>
                    <div id="preview-back" class="hidden mt-3">
                        <img id="img-back" src="" class="w-full h-32 object-cover rounded-lg">
                    </div>
                </div>

                <!-- Photo 3: Selfie -->
                <div id="card-selfie" class="border-2 border-gray-200 rounded-xl p-4 transition-all hover:border-blue-300 opacity-50">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold">3</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Selfie du client</h3>
                                <p class="text-xs text-gray-500">Photo du visage</p>
                            </div>
                        </div>
                        <svg id="check-selfie" class="w-6 h-6 text-green-500 hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <input
                        type="file"
                        id="input-selfie"
                        accept="image/*"
                        capture="user"
                        class="hidden"
                        disabled
                    >
                    <button
                        onclick="document.getElementById('input-selfie').click()"
                        id="btn-selfie"
                        disabled
                        class="w-full bg-gray-300 text-gray-500 font-medium py-3 rounded-lg transition flex items-center justify-center gap-2 cursor-not-allowed"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Prendre le selfie
                    </button>
                    <div id="preview-selfie" class="hidden mt-3">
                        <img id="img-selfie" src="" class="w-full h-32 object-cover rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="bg-white rounded-b-2xl px-6 py-6 shadow-xl">
                <button
                    id="btn-submit"
                    disabled
                    class="w-full bg-gray-300 text-gray-500 font-bold py-4 rounded-xl transition cursor-not-allowed text-lg"
                >
                    <span id="submit-text">Complétez les 3 photos</span>
                    <span id="submit-loading" class="hidden">Envoi en cours...</span>
                </button>
                <p class="text-xs text-center text-gray-500 mt-3">Les photos seront envoyées de manière sécurisée</p>
            </div>

            <!-- Success Message (Hidden) -->
            <div id="success-message" class="hidden bg-white rounded-2xl p-6 mt-4 text-center shadow-xl">
                <div class="w-20 h-20 bg-green-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">✅ Photos envoyées!</h2>
                <p class="text-gray-600">Vous pouvez fermer cette page</p>
            </div>
        </div>
    </div>

    <script>
        const token = "{{ $token }}";
        let photosData = {
            front: null,
            back: null,
            selfie: null
        };
        let photoCount = 0;

        // Mise à jour de la progression
        function updateProgress() {
            photoCount = Object.values(photosData).filter(p => p !== null).length;
            document.getElementById('progress-count').textContent = photoCount;
            document.getElementById('progress-bar').style.width = `${(photoCount / 3) * 100}%`;

            // Activer le bouton submit si 3 photos
            if (photoCount === 3) {
                const btnSubmit = document.getElementById('btn-submit');
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                btnSubmit.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white', 'cursor-pointer');
                document.getElementById('submit-text').textContent = 'Envoyer les photos ✓';
            }
        }

        // Gestion photo front
        document.getElementById('input-front').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    photosData.front = event.target.result;
                    document.getElementById('img-front').src = event.target.result;
                    document.getElementById('preview-front').classList.remove('hidden');
                    document.getElementById('check-front').classList.remove('hidden');
                    document.getElementById('btn-front').textContent = 'Photo capturée ✓';
                    document.getElementById('btn-front').classList.add('bg-green-600');

                    // Activer la carte suivante
                    document.getElementById('card-back').classList.remove('opacity-50');
                    document.getElementById('input-back').disabled = false;
                    document.getElementById('btn-back').disabled = false;
                    document.getElementById('btn-back').classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                    document.getElementById('btn-back').classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white');

                    updateProgress();
                };
                reader.readAsDataURL(file);
            }
        });

        // Gestion photo back
        document.getElementById('input-back').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    photosData.back = event.target.result;
                    document.getElementById('img-back').src = event.target.result;
                    document.getElementById('preview-back').classList.remove('hidden');
                    document.getElementById('check-back').classList.remove('hidden');
                    document.getElementById('btn-back').textContent = 'Photo capturée ✓';
                    document.getElementById('btn-back').classList.add('bg-green-600');

                    // Activer la carte suivante
                    document.getElementById('card-selfie').classList.remove('opacity-50');
                    document.getElementById('input-selfie').disabled = false;
                    document.getElementById('btn-selfie').disabled = false;
                    document.getElementById('btn-selfie').classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                    document.getElementById('btn-selfie').classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white');

                    updateProgress();
                };
                reader.readAsDataURL(file);
            }
        });

        // Gestion photo selfie
        document.getElementById('input-selfie').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    photosData.selfie = event.target.result;
                    document.getElementById('img-selfie').src = event.target.result;
                    document.getElementById('preview-selfie').classList.remove('hidden');
                    document.getElementById('check-selfie').classList.remove('hidden');
                    document.getElementById('btn-selfie').textContent = 'Photo capturée ✓';
                    document.getElementById('btn-selfie').classList.add('bg-green-600');

                    updateProgress();
                };
                reader.readAsDataURL(file);
            }
        });

        // Envoi des photos
        document.getElementById('btn-submit').addEventListener('click', function() {
            if (photoCount !== 3) return;

            // Afficher loading
            this.disabled = true;
            document.getElementById('submit-text').classList.add('hidden');
            document.getElementById('submit-loading').classList.remove('hidden');

            // Envoyer via POST
            fetch(`/clients/scan/${token}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    front: photosData.front,
                    back: photosData.back,
                    selfie: photosData.selfie
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Masquer le formulaire
                    document.querySelector('.container > div > div:not(#success-message)').style.display = 'none';

                    // Afficher le message de succès
                    document.getElementById('success-message').classList.remove('hidden');
                } else {
                    alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                    location.reload();
                }
            })
            .catch(err => {
                console.error('Erreur:', err);
                alert('Erreur de connexion. Veuillez réessayer.');
                location.reload();
            });
        });
    </script>
</body>
</html>
