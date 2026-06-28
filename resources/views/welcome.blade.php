<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KAYPA — Système d'Administration</title>
    <link rel="icon" type="image/png" href="{{ asset('kaypa.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex flex-col font-sans antialiased">

    {{-- ── Header ── --}}
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('kaypa.png') }}" alt="KAYPA" class="h-8 w-auto">
                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100 tracking-tight">KAYPA</span>
                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                    Système d'Administration
                </span>
            </div>
            @auth
            <a href="{{ url('/dashboard') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Tableau de bord
            </a>
            @endauth
        </div>
    </header>

    {{-- ── Contenu principal ── --}}
    <main class="flex-1 flex items-center">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
            <div class="flex flex-col-reverse lg:flex-row gap-10 lg:gap-16 items-start lg:items-center">

                {{-- ── Hero (gauche) ── --}}
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-6">
                        <img src="{{ asset('kaypa.png') }}" alt="KAYPA" class="w-16 h-16 rounded-xl shadow-lg">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
                                KAYPA Version 2
                            </h1>
                            <p class="text-gray-500 dark:text-gray-400 mt-0.5">
                                Plateforme de gestion bancaire — Haïti
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 mb-6"></div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-5 leading-relaxed">
                        Plateforme centralisée de gestion des opérations bancaires, des clients, des comptes et des transactions pour le réseau KAYPA en Haïti.
                    </p>

                    {{-- Modules clés --}}
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Comptes & Épargne</span>
                        </div>

                        <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/40 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Clients & KYC</span>
                        </div>

                        <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/40 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Transferts & Transactions</span>
                        </div>

                        <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/40 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Affiliés & Succursales</span>
                        </div>
                    </div>

                    <p class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Accès réservé au personnel autorisé de KAYPA
                    </p>
                </div>

                {{-- ── Card connexion (droite) ── --}}
                <div class="w-full lg:w-96 flex-shrink-0">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">

                        {{-- En-tête card --}}
                        <div class="bg-indigo-600 px-6 py-5 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-white font-semibold text-base leading-tight">Espace Sécurisé</h2>
                                <p class="text-indigo-200 text-xs mt-0.5">Authentification requise</p>
                            </div>
                        </div>

                        {{-- Corps card --}}
                        <div class="px-6 py-6">
                            @auth
                            <div class="flex items-center gap-3 mb-5 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                                <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ Auth::user()->role ?? 'Utilisateur' }}</p>
                                </div>
                            </div>
                            <a href="{{ url('/dashboard') }}"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors text-sm shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Accéder au tableau de bord
                            </a>
                            @else
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Accès Administrateur</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
                                Connectez-vous avec vos identifiants pour accéder à la plateforme de gestion KAYPA.
                            </p>
                            @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors text-sm shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Se connecter
                            </a>
                            @endif
                            @endauth
                        </div>

                        {{-- Footer card --}}
                        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                v2.0
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">Usage interne</span>
                        </div>
                    </div>

                    {{-- Note sécurité --}}
                    <div class="mt-4 flex items-start gap-2 px-1">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-gray-400 dark:text-gray-500 leading-relaxed">
                            Cette application est réservée au personnel autorisé de KAYPA. Tout accès non autorisé est interdit.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    {{-- ── Footer ── --}}
    <footer class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span class="text-xs text-gray-400 dark:text-gray-500">
                © {{ date('Y') }} KAYPA · Tous droits réservés
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Usage interne uniquement
            </span>
        </div>
    </footer>

</body>
</html>
