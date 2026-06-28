<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('kaypa.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
        <style>
            [x-cloak] { display: none !important; }
            .content-area { transition: padding-left 0.3s ease; }
            /* Padding par défaut avant Alpine (évite le flash au chargement) */
            @media (min-width: 1024px) { .content-area { padding-left: 16rem; } }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @auth
        <script src="{{ asset('js/session-manager.js') }}" defer></script>
        @endauth
    </head>
    <body class="font-sans antialiased">

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900"
             x-data="{
                 sidebarOpen: false,
                 collapsed: JSON.parse(localStorage.getItem('kaypa_sb') || 'false'),
                 toggle() { this.collapsed = !this.collapsed; localStorage.setItem('kaypa_sb', this.collapsed); }
             }"
             x-init="$watch('sidebarOpen', v => { if (v && window.innerWidth >= 1024) sidebarOpen = false; })">

            {{-- ── Sidebar ── --}}
            @include('layouts.navigation')

            {{-- ── Mobile top bar (< lg) ── --}}
            <div class="lg:hidden fixed top-0 inset-x-0 z-30 h-14 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center px-4 gap-3 shadow-sm">
                <button @click="sidebarOpen = true"
                        class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('kaypa.png') }}" alt="Kaypa" class="h-8 w-auto">
                </a>
                <div class="ml-auto">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 capitalize">
                        {{ Auth::user()->role ?? '' }}
                    </span>
                </div>
            </div>

            {{-- ── Zone de contenu principale ── --}}
            {{-- Sur desktop : pl-64 (sidebar pleine) ou pl-16 (sidebar réduite) --}}
            <div class="content-area pt-14 lg:pt-0"
                 :class="collapsed ? 'lg:pl-16' : ''">

                @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>

            </div>

        </div>

        @stack('scripts')
    </body>
</html>
