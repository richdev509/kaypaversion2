<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tableau de Bord') }} - KAYPA Version 2
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Message de bienvenue -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-2">Bienvenue, {{ Auth::user()->name }} !</h3>
                    <p class="text-blue-100">Système de Gestion des Carnets d'Épargne TIPA - Version 2.0</p>
                    <p class="text-sm text-blue-200 mt-2">Rôle: <span class="font-semibold">{{ Auth::user()->role ?? 'agent' }}</span></p>
                </div>
            </div>

            @if(in_array(Auth::user()->role, ['admin', 'comptable']))
            <!-- Filtre par succursale pour admin et comptable -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">📊 Filtrer les Statistiques</h3>
                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[250px]">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Succursale
                            </label>
                            <select name="branch_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Toutes les succursales</option>
                                @foreach(\App\Models\Branch::orderBy('name')->get() as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                            🔍 Filtrer
                        </button>
                        @if(request('branch_id'))
                        <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition">
                            ✖️ Réinitialiser
                        </a>
                        @endif
                    </form>
                </div>
            </div>
            @endif

            <!-- Statistiques en grille -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Card Clients Actifs (avec compte actif) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Clients Actifs
                                    @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                                        @if(request('branch_id'))
                                            ({{ \App\Models\Branch::find(request('branch_id'))->name }})
                                        @else
                                            (Toutes succursales)
                                        @endif
                                    @else
                                        ({{ Auth::user()->branch->name ?? 'Ma succursale' }})
                                    @endif
                                </p>
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">
                                    @php
                                        // Clients avec au moins un compte actif
                                        $clientsQuery = DB::table('clients');
                                        if(in_array(Auth::user()->role, ['admin', 'comptable'])) {
                                            if(request('branch_id')) {
                                                $clientsQuery->where('branch_id', request('branch_id'));
                                            }
                                        } else {
                                            $clientsQuery->where('branch_id', Auth::user()->branch_id);
                                        }
                                        $clientIds = $clientsQuery->pluck('id');
                                        $activeClientsCount = DB::table('accounts')
                                            ->whereIn('client_id', $clientIds)
                                            ->where('status', 'actif')
                                            ->distinct('client_id')
                                            ->count('client_id');
                                    @endphp
                                    {{ $activeClientsCount }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Clients Inactifs (sans compte actif) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Clients Inactifs
                                    @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                                        @if(request('branch_id'))
                                            ({{ \App\Models\Branch::find(request('branch_id'))->name }})
                                        @else
                                            (Toutes succursales)
                                        @endif
                                    @else
                                        ({{ Auth::user()->branch->name ?? 'Ma succursale' }})
                                    @endif
                                </p>
                                <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-2">
                                    @php
                                        // Total clients - clients actifs
                                        $totalClientsQuery = DB::table('clients');
                                        if(in_array(Auth::user()->role, ['admin', 'comptable'])) {
                                            if(request('branch_id')) {
                                                $totalClientsQuery->where('branch_id', request('branch_id'));
                                            }
                                        } else {
                                            $totalClientsQuery->where('branch_id', Auth::user()->branch_id);
                                        }
                                        $totalClients = $totalClientsQuery->count();
                                        $inactiveClientsCount = $totalClients - $activeClientsCount;
                                    @endphp
                                    {{ $inactiveClientsCount }}
                                </p>
                            </div>
                            <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                                <svg class="w-8 h-8 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Total Clients -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Clients
                                    @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                                        @if(request('branch_id'))
                                            ({{ \App\Models\Branch::find(request('branch_id'))->name }})
                                        @else
                                            (Toutes succursales)
                                        @endif
                                    @else
                                        ({{ Auth::user()->branch->name ?? 'Ma succursale' }})
                                    @endif
                                </p>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">
                                    @php
                                        $query = DB::table('clients');
                                        if(in_array(Auth::user()->role, ['admin', 'comptable'])) {
                                            if(request('branch_id')) {
                                                $query->where('branch_id', request('branch_id'));
                                            }
                                        } else {
                                            $query->where('branch_id', Auth::user()->branch_id);
                                        }
                                    @endphp
                                    {{ $query->count() }}
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Comptes Actifs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Comptes Actifs
                                    @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                                        @if(request('branch_id'))
                                            ({{ \App\Models\Branch::find(request('branch_id'))->name }})
                                        @else
                                            (Toutes succursales)
                                        @endif
                                    @else
                                        ({{ Auth::user()->branch->name ?? 'Ma succursale' }})
                                    @endif
                                </p>
                                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-2">
                                    @php
                                        $query = DB::table('accounts')->where('status', 'actif');
                                        if(in_array(Auth::user()->role, ['admin', 'comptable'])) {
                                            if(request('branch_id')) {
                                                $clientIds = DB::table('clients')->where('branch_id', request('branch_id'))->pluck('id');
                                                $query->whereIn('client_id', $clientIds);
                                            }
                                        } else {
                                            $clientIds = DB::table('clients')->where('branch_id', Auth::user()->branch_id)->pluck('id');
                                            $query->whereIn('client_id', $clientIds);
                                        }
                                    @endphp
                                    {{ $query->count() }}
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Transactions du jour -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Transactions Aujourd'hui
                                    @if(in_array(Auth::user()->role, ['admin', 'comptable']))
                                        @if(request('branch_id'))
                                            ({{ \App\Models\Branch::find(request('branch_id'))->name }})
                                        @else
                                            (Toutes succursales)
                                        @endif
                                    @else
                                        (Mes transactions)
                                    @endif
                                </p>
                                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mt-2">
                                    @php
                                        $query = DB::table('account_transactions')->whereDate('created_at', today());
                                        if(in_array(Auth::user()->role, ['admin', 'comptable'])) {
                                            if(request('branch_id')) {
                                                $clientIds = DB::table('clients')->where('branch_id', request('branch_id'))->pluck('id');
                                                $accountIds = DB::table('accounts')->whereIn('client_id', $clientIds)->pluck('id');
                                                $query->whereIn('account_id', $accountIds);
                                            }
                                        } else {
                                            $query->where('created_by', Auth::id());
                                        }
                                    @endphp
                                    {{ $query->count() }}
                                </p>
                            </div>
                            <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                                <svg class="w-8 h-8 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recherche Client -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Rechercher un Client</h3>
                    <form action="{{ route('clients.search') }}" method="GET" class="flex gap-3">
                        <div class="flex-1">
                            <input
                                type="text"
                                name="search"
                                placeholder="Rechercher par nom, prénom, téléphone, email ou numéro de carte..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                value="{{ request('search') }}"
                            >
                        </div>
                        <button
                            type="submit"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Rechercher
                        </button>
                    </form>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Actions Rapides</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="{{ route('clients.create') }}" class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Nouveau Client</span>
                        </a>

                        <a href="{{ route('accounts.create') }}" class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/40 transition">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Nouveau Compte</span>
                        </a>

                        <a href="{{ route('clients.index') }}" class="flex items-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Liste Clients</span>
                        </a>

                        <a href="{{ route('accounts.index') }}" class="flex items-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/40 transition">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Liste Comptes</span>
                        </a>
                    </div>
                </div>
            </div>

            @if(in_array(Auth::user()->role, ['admin', 'comptable']))
            <!-- Programme Scolaire -->
            @php
                $schoolProgramsActifs      = DB::table('school_programs')->where('status', 'actif')->count();
                $schoolTotalInscrits       = DB::table('school_program_enrollments')->count();
                $schoolCouponsActifs       = DB::table('school_program_enrollments')->where('coupon_status', 'active')->count();
                $schoolCouponsUtilisesAuj  = DB::table('school_program_enrollments')
                                                ->where('coupon_status', 'used')
                                                ->whereDate('used_at', today())
                                                ->count();
                $schoolProgrammesCourants  = DB::table('school_programs')
                                                ->where('status', 'actif')
                                                ->orderByDesc('inscription_debut')
                                                ->limit(5)
                                                ->get();
            @endphp
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            🎓 Programme Scolaire
                        </h3>
                        <a href="{{ route('school-programs.index') }}"
                           class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            Gérer les programmes →
                        </a>
                    </div>

                    {{-- KPI cards --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">Programmes actifs</p>
                            <p class="text-3xl font-bold text-indigo-700 dark:text-indigo-300 mt-1">{{ $schoolProgramsActifs }}</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide">Total inscrits</p>
                            <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-1">{{ $schoolTotalInscrits }}</p>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wide">Coupons actifs</p>
                            <p class="text-3xl font-bold text-yellow-700 dark:text-yellow-300 mt-1">{{ $schoolCouponsActifs }}</p>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                            <p class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wide">Utilisés aujourd'hui</p>
                            <p class="text-3xl font-bold text-purple-700 dark:text-purple-300 mt-1">{{ $schoolCouponsUtilisesAuj }}</p>
                        </div>
                    </div>

                    @if($schoolProgrammesCourants->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide border-b border-gray-200 dark:border-gray-700">
                                    <th class="pb-2 pr-4">Programme</th>
                                    <th class="pb-2 pr-4">Inscription</th>
                                    <th class="pb-2 pr-4">Coupons actifs</th>
                                    <th class="pb-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($schoolProgrammesCourants as $sp)
                                @php
                                    $spInscrits = DB::table('school_program_enrollments')
                                        ->where('school_program_id', $sp->id)
                                        ->where('coupon_status', 'active')
                                        ->count();
                                    $now = now();
                                    $inscriptionOuverte = $sp->inscription_debut && $sp->inscription_fin
                                        && $now->between(\Carbon\Carbon::parse($sp->inscription_debut), \Carbon\Carbon::parse($sp->inscription_fin));
                                @endphp
                                <tr>
                                    <td class="py-2.5 pr-4 font-medium text-gray-900 dark:text-gray-100">
                                        {{ $sp->name }}
                                        @if($inscriptionOuverte)
                                            <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Inscriptions ouvertes</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 pr-4 text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($sp->inscription_debut)->format('d/m/Y') }}
                                        → {{ \Carbon\Carbon::parse($sp->inscription_fin)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-2.5 pr-4 font-semibold text-gray-900 dark:text-gray-100">{{ $spInscrits }}</td>
                                    <td class="py-2.5 text-right">
                                        <a href="{{ route('school-programs.show', $sp->id) }}"
                                           class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                            Détail →
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aucun programme scolaire actif.
                        <a href="{{ route('school-programs.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Créer un programme →</a>
                    </p>
                    @endif

                    {{-- Actions rapides programme --}}
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-3">
                        <a href="{{ route('school-programs.create') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            ➕ Nouveau programme
                        </a>
                        <a href="{{ route('coupon.verify.form') }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 transition">
                            🔍 Page vérification coupon
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Info système -->
            <div class="mt-6 bg-gray-50 dark:bg-gray-900/50 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Base de données: <span class="font-semibold">{{ config('database.connections.mysql.database') }}</span>
                        | Host: <span class="font-semibold">{{ config('database.connections.mysql.host') }}</span>
                        | Version: <span class="font-semibold">{{ env('APP_VERSION', 'v2') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
