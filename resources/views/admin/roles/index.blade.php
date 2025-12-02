<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔐 {{ __('Gestion des Rôles') }}
            </h2>
            <a href="{{ route('admin.roles.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                ➕ Créer un Rôle
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Liste des Rôles ({{ $roles->count() }})</h3>
                        <a href="{{ route('admin.permissions.index') }}" class="text-blue-600 hover:text-blue-800">
                            📋 Gérer les Permissions
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rôle
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Permissions
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Utilisateurs
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($roles as $role)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $role->name }}
                                                    @if(in_array($role->name, ['admin', 'manager', 'agent']))
                                                        <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                                            Système
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $role->permissions_count }} {{ $role->permissions_count > 1 ? 'permissions' : 'permission' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $role->users_count }} {{ $role->users_count > 1 ? 'utilisateurs' : 'utilisateur' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                ✏️ Éditer
                                            </a>
                                            @if(!in_array($role->name, ['admin', 'manager', 'agent']))
                                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                                        🗑️ Supprimer
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400">🔒 Protégé</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($roles->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            Aucun rôle créé. Cliquez sur "Créer un Rôle" pour commencer.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Rôles</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $roles->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Rôles Système</div>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $roles->whereIn('name', ['admin', 'manager', 'agent'])->count() }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Rôles Personnalisés</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ $roles->whereNotIn('name', ['admin', 'manager', 'agent'])->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
