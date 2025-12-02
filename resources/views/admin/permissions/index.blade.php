<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📋 {{ __('Gestion des Permissions') }}
            </h2>
            <a href="{{ route('admin.roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Retour aux Rôles
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

            <!-- Formulaire création permission -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">➕ Créer une Nouvelle Permission</h3>
                    <form action="{{ route('admin.permissions.store') }}" method="POST" class="flex gap-3">
                        @csrf
                        <div class="flex-1">
                            <input type="text" name="name"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Ex: articles.publish, reports.export, etc."
                                   required>
                            <p class="mt-1 text-xs text-gray-500">
                                Format recommandé: module.action (ex: clients.delete, fund-movements.approve)
                            </p>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                            Créer
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des permissions groupées -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">
                        Liste des Permissions ({{ $permissions->flatten()->count() }})
                    </h3>

                    <div class="space-y-6">
                        @foreach($permissions as $group => $perms)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-3 text-lg capitalize">
                                    📁 {{ str_replace('-', ' ', $group) }}
                                    <span class="text-sm text-gray-500 font-normal">({{ $perms->count() }} permissions)</span>
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Nom
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Rôles Assignés
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($perms as $permission)
                                                <tr>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <span class="text-sm font-mono text-gray-900">{{ $permission->name }}</span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="text-sm text-gray-900">
                                                            {{ $permission->roles_count }} {{ $permission->roles_count > 1 ? 'rôles' : 'rôle' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                        <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette permission ?')">
                                                                🗑️ Supprimer
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($permissions->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            Aucune permission créée. Les permissions existantes sont chargées automatiquement depuis la base de données.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Permissions</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $permissions->flatten()->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Modules</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $permissions->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Moyenne/Module</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ $permissions->count() > 0 ? round($permissions->flatten()->count() / $permissions->count(), 1) : 0 }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
