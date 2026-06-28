{{-- ═══════════════════════════════════════════════════
     OVERLAY mobile (clique → ferme sidebar)
══════════════════════════════════════════════════════ --}}
<div x-show="sidebarOpen" x-cloak
     @click="sidebarOpen = false"
     @resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 lg:hidden">
</div>

{{-- ═══════════════════════════════════════════════════
     SIDEBAR
     - Mobile  : overlay, toujours w-72, masqué par défaut
     - Desktop : fixe, w-64 (expanded) ou w-16 (collapsed)
══════════════════════════════════════════════════════ --}}
<aside :class="[
           sidebarOpen ? '!translate-x-0' : '',
           collapsed    ? 'lg:w-16'        : 'lg:w-64'
       ]"
       class="fixed inset-y-0 left-0 z-50 w-72 h-screen -translate-x-full lg:translate-x-0
              bg-white dark:bg-gray-900
              border-r border-gray-200 dark:border-gray-700
              flex flex-col transition-all duration-300 ease-in-out overflow-hidden">

    {{-- ── En-tête : logo + boutons ── --}}
    <div class="h-16 flex items-center border-b border-gray-200 dark:border-gray-700 shrink-0 transition-all duration-300"
         :class="collapsed ? 'px-2 justify-center' : 'px-4 justify-between'">

        {{-- Logo mobile (toujours visible sur mobile, jamais sur desktop) --}}
        <a href="{{ route('dashboard') }}"
           class="lg:hidden flex items-center gap-2 min-w-0">
            <img src="{{ asset('kaypa.png') }}" alt="Kaypa" class="h-9 w-auto shrink-0">
        </a>

        {{-- Logo desktop (visible quand sidebar expanded, caché quand collapsed) --}}
        <a href="{{ route('dashboard') }}"
           class="hidden lg:flex items-center gap-2 min-w-0"
           :class="collapsed ? '!hidden' : ''">
            <img src="{{ asset('kaypa.png') }}" alt="Kaypa" class="h-9 w-auto shrink-0">
        </a>

        {{-- Bouton collapse/expand — desktop uniquement (hidden lg:flex = jamais de conflit x-show) --}}
        <button @click="toggle()"
                :title="collapsed ? 'Développer le menu' : 'Réduire le menu'"
                class="hidden lg:flex items-center justify-center w-8 h-8 rounded-md
                       text-gray-400 dark:text-gray-500
                       hover:text-gray-700 dark:hover:text-gray-200
                       hover:bg-gray-100 dark:hover:bg-gray-800
                       transition-colors shrink-0">
            <svg class="w-4 h-4 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>

        {{-- Bouton fermer — mobile uniquement, PAS de x-show (évite le conflit inline style vs lg:hidden) --}}
        <button @click="sidebarOpen = false"
                class="lg:hidden p-1.5 rounded-md text-gray-400
                       hover:text-gray-600 dark:hover:text-gray-200
                       hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Navigation (scrollable) ── --}}
    <nav class="flex-1 overflow-y-auto py-3 space-y-0.5" :class="collapsed ? 'px-1.5' : 'px-2'">

        {{-- ═══ SECTION : PRINCIPAL ═══ --}}
        <p x-show="!collapsed"
           class="px-3 pt-1 pb-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            Principal
        </p>

        {{-- Dashboard --}}
        @php $isDash = request()->routeIs('dashboard') && !request()->routeIs('dashboard.*'); @endphp
        <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" title="Dashboard"
           class="flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $isDash
                     ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                     : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
           :class="collapsed ? 'justify-center px-2' : 'gap-2.5 px-3'">
            <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span x-show="!collapsed" class="truncate">Dashboard</span>
        </a>

        {{-- Clients --}}
        @php $isClients = request()->routeIs('clients.*'); @endphp
        <a href="{{ route('clients.index') }}" @click="sidebarOpen = false" title="Clients"
           class="flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $isClients
                     ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                     : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
           :class="collapsed ? 'justify-center px-2' : 'gap-2.5 px-3'">
            <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-show="!collapsed" class="truncate">Clients</span>
        </a>

        {{-- Comptes --}}
        @php $isAccounts = request()->routeIs('accounts.*'); @endphp
        <a href="{{ route('accounts.index') }}" @click="sidebarOpen = false" title="Comptes"
           class="flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $isAccounts
                     ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                     : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
           :class="collapsed ? 'justify-center px-2' : 'gap-2.5 px-3'">
            <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <span x-show="!collapsed" class="truncate">Comptes</span>
        </a>

        {{-- ═══ SECTION : COMPTES BANCAIRES ═══ --}}
        <p x-show="!collapsed"
           class="px-3 pt-4 pb-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            Comptes Bancaires
        </p>
        <div x-show="collapsed" class="pt-3 pb-1 mx-2 border-t border-gray-200 dark:border-gray-700"></div>

        {{-- ─── C. Courants (accordion) ─── --}}
        @php $ccActive = request()->routeIs('current-accounts.*'); @endphp
        <div x-data="{ open: {{ $ccActive ? 'true' : 'false' }} }">
            <button @click="collapsed ? (window.location.href = '{{ route('current-accounts.index') }}') : (open = !open)"
                    title="Comptes Courants"
                    class="w-full flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $ccActive
                              ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
                    :class="collapsed ? 'justify-center px-2' : 'justify-between px-3'">
                <span class="flex items-center" :class="collapsed ? '' : 'gap-2.5'">
                    <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span x-show="!collapsed" class="truncate">C. Courants</span>
                </span>
                <svg x-show="!collapsed"
                     class="w-3.5 h-3.5 transition-transform duration-150 shrink-0 text-gray-400"
                     :class="open ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open && !collapsed" x-transition
                 class="mt-0.5 ml-3 pl-3 border-l-2 border-gray-200 dark:border-gray-700 space-y-0.5">
                <a href="{{ route('current-accounts.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('current-accounts.index') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📋 Liste des comptes
                </a>
                @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
                <a href="{{ route('current-accounts.create') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    ➕ Ouvrir un compte
                </a>
                @endif
                @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                <a href="{{ route('current-accounts.dashboard') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('current-accounts.dashboard') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('current-accounts.report') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('current-accounts.report') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📈 Rapport financier
                </a>
                @endif
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('current-accounts.settings') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    ⚙️ Paramètres
                </a>
                @endif
            </div>
        </div>

        {{-- ─── C. Épargne (accordion) ─── --}}
        @php $ceActive = request()->routeIs('savings-accounts.*'); @endphp
        <div x-data="{ open: {{ $ceActive ? 'true' : 'false' }} }">
            <button @click="collapsed ? (window.location.href = '{{ route('savings-accounts.index') }}') : (open = !open)"
                    title="Comptes Épargne"
                    class="w-full flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $ceActive
                              ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
                    :class="collapsed ? 'justify-center px-2' : 'justify-between px-3'">
                <span class="flex items-center" :class="collapsed ? '' : 'gap-2.5'">
                    <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!collapsed" class="truncate">C. Épargne</span>
                </span>
                <svg x-show="!collapsed"
                     class="w-3.5 h-3.5 transition-transform duration-150 shrink-0 text-gray-400"
                     :class="open ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open && !collapsed" x-transition
                 class="mt-0.5 ml-3 pl-3 border-l-2 border-gray-200 dark:border-gray-700 space-y-0.5">
                <a href="{{ route('savings-accounts.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('savings-accounts.index') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📋 Liste des comptes
                </a>
                @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
                <a href="{{ route('savings-accounts.create') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    ➕ Ouvrir un compte
                </a>
                @endif
                @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                <a href="{{ route('savings-accounts.dashboard') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('savings-accounts.dashboard') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('savings-accounts.report') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('savings-accounts.report') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📈 Rapport financier
                </a>
                @endif
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('savings-accounts.settings') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    ⚙️ Paramètres
                </a>
                @endif
            </div>
        </div>

        {{-- ═══ SECTION : OPÉRATIONS ═══ --}}
        <p x-show="!collapsed"
           class="px-3 pt-4 pb-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            Opérations
        </p>
        <div x-show="collapsed" class="pt-3 pb-1 mx-2 border-t border-gray-200 dark:border-gray-700"></div>

        {{-- ─── Transferts (accordion) ─── --}}
        @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
        @php $trActive = request()->routeIs('transfers.*'); @endphp
        <div x-data="{ open: {{ $trActive ? 'true' : 'false' }} }">
            <button @click="collapsed ? (window.location.href = '{{ route('transfers.index') }}') : (open = !open)"
                    title="Transferts"
                    class="w-full flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $trActive
                              ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
                    :class="collapsed ? 'justify-center px-2' : 'justify-between px-3'">
                <span class="flex items-center" :class="collapsed ? '' : 'gap-2.5'">
                    <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span x-show="!collapsed" class="truncate">Transferts</span>
                </span>
                <svg x-show="!collapsed"
                     class="w-3.5 h-3.5 transition-transform duration-150 shrink-0 text-gray-400"
                     :class="open ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open && !collapsed" x-transition
                 class="mt-0.5 ml-3 pl-3 border-l-2 border-gray-200 dark:border-gray-700 space-y-0.5">
                <a href="{{ route('transfers.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    🔍 Recherche
                </a>
                @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                <a href="{{ route('transfers.all') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    📋 Liste complète
                </a>
                @endif
                @if(Auth::user()->isAdmin() || Auth::user()->hasRole('comptable'))
                <a href="{{ route('transfers.stats') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    📊 Statistiques
                </a>
                <a href="{{ route('transfers.reports') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    📈 Rapports
                </a>
                @endif
                @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                <a href="{{ route('transfers.disputes') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    ⚠️ Litiges
                </a>
                @endif
                @if(Auth::user()->isAdmin())
                <a href="{{ route('transfers.settings') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200 transition-colors">
                    ⚙️ Paramètres
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- ─── Finance (accordion) ─── --}}
        @php
            $financeActive = request()->routeIs('reports.*')
                          || request()->routeIs('fund-movements.*')
                          || request()->routeIs('branch-cash.*')
                          || request()->routeIs('online-payments.*')
                          || request()->routeIs('affiliates.*')
                          || request()->routeIs('client-access.*')
                          || request()->routeIs('admin.withdrawals.*');
        @endphp
        <div x-data="{ open: {{ $financeActive ? 'true' : 'false' }} }">
            <button @click="collapsed ? (window.location.href = '{{ route('reports.index') }}') : (open = !open)"
                    title="Finance"
                    class="w-full flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $financeActive
                              ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
                    :class="collapsed ? 'justify-center px-2' : 'justify-between px-3'">
                <span class="flex items-center" :class="collapsed ? '' : 'gap-2.5'">
                    <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="!collapsed" class="truncate">Finance</span>
                </span>
                <svg x-show="!collapsed"
                     class="w-3.5 h-3.5 transition-transform duration-150 shrink-0 text-gray-400"
                     :class="open ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open && !collapsed" x-transition
                 class="mt-0.5 ml-3 pl-3 border-l-2 border-gray-200 dark:border-gray-700 space-y-0.5">
                @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
                <a href="{{ route('reports.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('reports.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📋 Rapports
                </a>
                @endif
                @if(Auth::user()->hasPermissionTo('fund-movements.view'))
                <a href="{{ route('fund-movements.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('fund-movements.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    💹 Gestion Financière
                </a>
                @endif
                @if(Auth::user()->hasPermissionTo('branch-cash.view'))
                <a href="{{ route('branch-cash.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('branch-cash.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    💵 Caisse Succursale
                </a>
                @endif
                @if(Auth::user()->isAdmin() || Auth::user()->hasRole('comptable'))
                <a href="{{ route('online-payments.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('online-payments.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    💳 Paiements Online
                </a>
                <a href="{{ route('affiliates.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('affiliates.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    👥 Affiliés
                </a>
                <a href="{{ route('admin.withdrawals.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('admin.withdrawals.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    💰 Demandes Retrait
                </a>
                @endif
                @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
                <a href="{{ route('client-access.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('client-access.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    🔑 Accès Client
                </a>
                @endif
            </div>
        </div>

        {{-- ═══ SECTION : BUSINESS ═══ --}}
        <p x-show="!collapsed"
           class="px-3 pt-4 pb-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            Business
        </p>
        <div x-show="collapsed" class="pt-3 pb-1 mx-2 border-t border-gray-200 dark:border-gray-700"></div>

        {{-- ─── Business (accordion) ─── --}}
        @php
            $bizActive = request()->routeIs('business.*') || request()->routeIs('school-programs.*');
        @endphp
        <div x-data="{ open: {{ $bizActive ? 'true' : 'false' }} }">
            <button @click="collapsed ? (window.location.href = '{{ route('business.entities.index') }}') : (open = !open)"
                    title="Business"
                    class="w-full flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $bizActive
                              ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
                    :class="collapsed ? 'justify-center px-2' : 'justify-between px-3'">
                <span class="flex items-center" :class="collapsed ? '' : 'gap-2.5'">
                    <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="!collapsed" class="truncate">Business</span>
                </span>
                <svg x-show="!collapsed"
                     class="w-3.5 h-3.5 transition-transform duration-150 shrink-0 text-gray-400"
                     :class="open ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open && !collapsed" x-transition
                 class="mt-0.5 ml-3 pl-3 border-l-2 border-gray-200 dark:border-gray-700 space-y-0.5">
                <a href="{{ route('business.entities.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('business.entities.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    🏢 Entreprises
                </a>
                <a href="{{ route('business.credit.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('business.credit.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    💳 Crédit
                </a>
                <a href="{{ route('business.credit.alerts') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('business.credit.alerts') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    ⚠️ Alertes Crédit
                </a>
                <a href="{{ route('business.payroll.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('business.payroll.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    💼 Payroll
                </a>
                @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                <a href="{{ route('business.credit.plans') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('business.credit.plans') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📊 Plans de taux
                </a>
                <a href="{{ route('school-programs.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('school-programs.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    🎓 Programme Scolaire
                </a>
                @endif
            </div>
        </div>

        {{-- ═══ SECTION : ADMINISTRATION ═══ --}}
        @if(Auth::user()->isAdmin() || Auth::user()->isManager())
        @php
            $adminActive = request()->routeIs('branches.*')
                        || request()->routeIs('users.*')
                        || request()->routeIs('admin.roles.*')
                        || request()->routeIs('activity-logs.*')
                        || request()->routeIs('dashboard.analytics');
        @endphp
        <p x-show="!collapsed"
           class="px-3 pt-4 pb-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            Administration
        </p>
        <div x-show="collapsed" class="pt-3 pb-1 mx-2 border-t border-gray-200 dark:border-gray-700"></div>

        <div x-data="{ open: {{ $adminActive ? 'true' : 'false' }} }">
            <button @click="collapsed ? (window.location.href = '{{ route('branches.index') }}') : (open = !open)"
                    title="Administration"
                    class="w-full flex items-center py-2 rounded-lg text-sm font-medium transition-colors
                           {{ $adminActive
                              ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                              : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}"
                    :class="collapsed ? 'justify-center px-2' : 'justify-between px-3'">
                <span class="flex items-center" :class="collapsed ? '' : 'gap-2.5'">
                    <svg style="width:1.1rem;height:1.1rem" class="shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="!collapsed" class="truncate">Administration</span>
                </span>
                <svg x-show="!collapsed"
                     class="w-3.5 h-3.5 transition-transform duration-150 shrink-0 text-gray-400"
                     :class="open ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div x-show="open && !collapsed" x-transition
                 class="mt-0.5 ml-3 pl-3 border-l-2 border-gray-200 dark:border-gray-700 space-y-0.5">
                @if(Auth::user()->hasPermissionTo('dashboard.view'))
                <a href="{{ route('dashboard.analytics') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('dashboard.analytics') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    📊 Analytics
                </a>
                @endif
                <a href="{{ route('branches.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('branches.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    🏢 Branches
                </a>
                <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('users.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    👤 Utilisateurs
                </a>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.roles.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('admin.roles.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    🔐 Rôles & Permissions
                </a>
                <a href="{{ route('activity-logs.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm transition-colors
                          {{ request()->routeIs('activity-logs.*') ? 'text-indigo-600 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200' }}">
                    🔎 Monitoring
                </a>
                @endif
            </div>
        </div>
        @endif

    </nav>

    {{-- ── Bas du sidebar : profil + déconnexion toujours visibles ── --}}
    <div class="border-t border-gray-200 dark:border-gray-700 shrink-0">

        {{-- Mode EXPANDED : info + boutons côte à côte ── --}}
        <div x-show="!collapsed" class="p-3 space-y-2">

            {{-- Carte info utilisateur --}}
            <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800">
                <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold select-none shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate leading-tight">{{ Auth::user()->name }}</p>
                    <span class="inline-block mt-0.5 px-1.5 py-px rounded text-xs font-medium bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 capitalize leading-tight">{{ Auth::user()->role }}</span>
                </div>
            </div>

            {{-- Boutons Profil + Déconnexion ── --}}
            <div class="flex gap-2">
                <a href="{{ route('profile.edit') }}" @click="sidebarOpen = false"
                   class="flex-1 flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-lg text-xs font-medium
                          text-gray-500 dark:text-gray-400
                          hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-200
                          border border-gray-200 dark:border-gray-700 transition-colors">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Mon profil
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-lg text-xs font-medium
                                   text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300
                                   hover:bg-red-50 dark:hover:bg-red-900/20
                                   border border-red-200 dark:border-red-900/50 transition-colors">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>

        {{-- Mode COLLAPSED : avatar + icône déconnexion ── --}}
        <div x-show="collapsed" class="p-2 space-y-1">
            <a href="{{ route('profile.edit') }}" @click="sidebarOpen = false"
               title="{{ Auth::user()->name }} — {{ Auth::user()->role }}"
               class="flex justify-center p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors">
                <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold select-none">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Déconnexion"
                        class="w-full flex items-center justify-center p-2 rounded-lg
                               text-red-600 dark:text-red-400
                               hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>

    </div>

</aside>
