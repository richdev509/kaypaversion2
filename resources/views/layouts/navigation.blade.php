<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('kaypa.png') }}" alt="Kaypa Logo" class="block h-9 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if(Auth::user()->hasPermissionTo('dashboard.view'))
                    <x-nav-link :href="route('dashboard.analytics')" :active="request()->routeIs('dashboard.analytics')">
                        📊 Analytics
                    </x-nav-link>
                    @endif
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        Clients
                    </x-nav-link>
                    <x-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                        Comptes
                    </x-nav-link>

                    <!-- Menu déroulant Transferts -->
                    @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
                    <x-dropdown align="top" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out">
                                <span>💸 Transferts</span>
                                <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('transfers.index')">
                                🔍 Recherche Transfert
                            </x-dropdown-link>
                            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                            <x-dropdown-link :href="route('transfers.all')">
                                📋 Liste Complète
                            </x-dropdown-link>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->hasRole('comptable'))
                            <x-dropdown-link :href="route('transfers.stats')">
                                📊 Statistiques
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('transfers.reports')">
                                📈 Rapports (CSV/PDF)
                            </x-dropdown-link>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                            <x-dropdown-link :href="route('transfers.disputes')">
                                ⚠️ Gestion des Litiges
                            </x-dropdown-link>
                            @endif
                            @if(Auth::user()->isAdmin())
                            <x-dropdown-link :href="route('transfers.settings')">
                                ⚙️ Paramètres
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                    @endif

                    @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        Rapports
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->hasPermissionTo('fund-movements.view'))
                    <x-nav-link :href="route('fund-movements.index')" :active="request()->routeIs('fund-movements.*')">
                        Gestion Financière
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->hasPermissionTo('branch-cash.view'))
                    <x-nav-link :href="route('branch-cash.index')" :active="request()->routeIs('branch-cash.*')">
                        💰 Caisse
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->isAdmin() || Auth::user()->hasRole('comptable'))
                    <x-nav-link :href="route('online-payments.index')" :active="request()->routeIs('online-payments.*')">
                        💳 Paiements
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->isAdmin() || Auth::user()->hasRole('comptable'))
                    <x-nav-link :href="route('affiliates.index')" :active="request()->routeIs('affiliates.*')">
                        👥 Affiliés
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
                    <x-nav-link :href="route('client-access.index')" :active="request()->routeIs('client-access.*')">
                        🔑 Accès
                    </x-nav-link>
                    @endif

                    <!-- Menu déroulant Administration -->
                    @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                    <x-dropdown align="top" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out">
                                <span>⚙️ Administration</span>
                                <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                            <x-dropdown-link :href="route('branches.index')">
                                🏢 Branches
                            </x-dropdown-link>
                            @endif
                            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                            <x-dropdown-link :href="route('users.index')">
                                👤 Utilisateurs
                            </x-dropdown-link>
                            @endif
                            @if(Auth::user()->isAdmin())
                            <x-dropdown-link :href="route('admin.roles.index')">
                                🔐 Rôles & Permissions
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(Auth::user()->hasPermissionTo('dashboard.view'))
            <x-responsive-nav-link :href="route('dashboard.analytics')" :active="request()->routeIs('dashboard.analytics')">
                📊 Analytics
            </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                Clients
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                Comptes
            </x-responsive-nav-link>
            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <x-responsive-nav-link :href="route('branches.index')" :active="request()->routeIs('branches.*')">
                Branches
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin() || Auth::user()->isManager())
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                Utilisateurs
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin())
            <x-responsive-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.*')">
                🔐 Rôles & Permissions
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                Rapports
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->hasPermissionTo('fund-movements.view'))
            <x-responsive-nav-link :href="route('fund-movements.index')" :active="request()->routeIs('fund-movements.*')">
                Gestion Financière
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->hasPermissionTo('branch-cash.view'))
            <x-responsive-nav-link :href="route('branch-cash.index')" :active="request()->routeIs('branch-cash.*')">
                💰 Caisse Succursale
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin() || Auth::user()->isManager() || Auth::user()->isAgent())
            <x-responsive-nav-link :href="route('client-access.index')" :active="request()->routeIs('client-access.*')">
                🔑 Accès Client
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin() || Auth::user()->hasRole('comptable'))
            <x-responsive-nav-link :href="route('affiliates.index')" :active="request()->routeIs('affiliates.*')">
                👥 Affiliés
            </x-responsive-nav-link>
            @endif
            @if(Auth::user()->isAdmin() || Auth::user()->hasRole('comptable'))
            <x-responsive-nav-link :href="route('online-payments.index')" :active="request()->routeIs('online-payments.*')">
                💳 Paiements Online
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
