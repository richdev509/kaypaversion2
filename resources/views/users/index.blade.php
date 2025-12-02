<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestion des Utilisateurs
            </h2>
            <div class="flex gap-2">
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('users.roles-permissions') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    🔐 Rôles & Permissions
                </a>
                @endif
                @can('create', App\Models\User::class)
                    <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        + Nouvel Utilisateur
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                @foreach($roles as $roleKey => $roleName)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $roleName }}</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">
                                        {{ $users->where('role', $roleKey)->count() }}
                                    </p>
                                </div>
                                <div class="p-3
                                    @if($roleKey === 'admin') bg-red-100 dark:bg-red-900
                                    @elseif($roleKey === 'manager') bg-blue-100 dark:bg-blue-900
                                    @elseif($roleKey === 'agent') bg-green-100 dark:bg-green-900
                                    @else bg-gray-100 dark:bg-gray-900
                                    @endif rounded-full">
                                    <svg class="w-8 h-8
                                        @if($roleKey === 'admin') text-red-600 dark:text-red-300
                                        @elseif($roleKey === 'manager') text-blue-600 dark:text-blue-300
                                        @elseif($roleKey === 'agent') text-green-600 dark:text-green-300
                                        @else text-gray-600 dark:text-gray-300
                                        @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Recherche et Filtres -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">🔍 Rechercher un Utilisateur</h3>
                    <form action="{{ route('users.index') }}" method="GET" id="searchForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <input
                                type="text"
                                name="search"
                                placeholder="Rechercher par nom, email ou téléphone..."
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                value="{{ request('search') }}"
                                oninput="document.getElementById('searchForm').submit()"
                            >
                        </div>

                        <div>
                            <select
                                name="role"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                onchange="document.getElementById('searchForm').submit()"
                            >
                                <option value="">Tous les rôles</option>
                                @foreach($roles as $roleKey => $roleName)
                                    <option value="{{ $roleKey }}" {{ request('role') === $roleKey ? 'selected' : '' }}>
                                        {{ $roleName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <select
                                name="branch_id"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                onchange="document.getElementById('searchForm').submit()"
                            >
                                <option value="">Toutes les branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Utilisateur
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Rôle
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Branche
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Date Création
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                <span class="text-gray-700 font-semibold text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <a href="{{ route('users.show', $user) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                    {{ $user->name }}
                                                </a>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $user->email }}
                                                </p>
                                                @if($user->telephone)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        📞 {{ $user->telephone }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                                            @if($user->role === 'admin') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                                            @elseif($user->role === 'manager') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                                            @elseif($user->role === 'agent') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                                            @elseif($user->role === 'support') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                                            @endif">
                                            {{ $user->role_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $user->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $user->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @can('view', $user)
                                            <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                                Voir
                                            </a>
                                        @endcan
                                        @can('update', $user)
                                            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Modifier
                                            </a>
                                        @endcan
                                        @can('delete', $user)
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Supprimer
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucun utilisateur</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Commencez par créer un nouvel utilisateur.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
