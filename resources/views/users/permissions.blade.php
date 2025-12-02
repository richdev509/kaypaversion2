<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gérer les Permissions de {{ $user->name }}
            </h2>
            <a href="{{ route('users.show', $user) }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Informations de l'utilisateur
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Nom:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Email:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Rôle:</span>
                            <span class="ml-2 px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $user->role }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Succursale:</span>
                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $user->branch->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton pour réinitialiser aux permissions du rôle -->
            @if(count($userDirectPermissions) > 0)
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-300 dark:border-orange-700 rounded-lg p-4 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-orange-800 dark:text-orange-300 mb-1">
                                ⚠️ Cet utilisateur a des permissions personnalisées
                            </h4>
                            <p class="text-sm text-orange-700 dark:text-orange-400 mb-3">
                                Les permissions personnalisées remplacent les permissions du rôle <strong>{{ $user->role }}</strong>.
                                Pour revenir aux permissions par défaut du rôle, cliquez sur le bouton ci-dessous.
                            </p>
                            <form action="{{ route('users.permissions.reset', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir réinitialiser aux permissions du rôle ? Les permissions personnalisées seront supprimées.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition">
                                    🔄 Réinitialiser aux permissions du rôle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('users.permissions.update', $user) }}" method="POST">
                @csrf

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                Permissions Personnalisées
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="inline-block w-4 h-4 bg-green-200 border border-green-400 rounded mr-2"></span> = Permission du rôle (automatique)
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <span class="inline-block w-4 h-4 bg-blue-100 border-2 border-blue-500 rounded mr-2"></span> = Permission personnalisée (ajoutée directement)
                            </p>
                            <p class="text-sm bg-yellow-50 border border-yellow-300 text-yellow-800 dark:bg-yellow-900/20 dark:border-yellow-700 dark:text-yellow-400 mt-3 p-3 rounded-lg">
                                ℹ️ <strong>Comment ça marche :</strong><br>
                                • Les cases cochées en <strong class="text-green-600">VERT</strong> sont les permissions du rôle <strong>{{ $user->role }}</strong><br>
                                • Vous pouvez <strong>ajouter</strong> des permissions supplémentaires en cochant d'autres cases<br>
                                • Vous pouvez <strong>retirer</strong> des permissions du rôle en décochant les cases vertes<br>
                                • Les permissions cochées seront sauvegardées comme permissions personnalisées<br>
                                • ⚠️ Une fois personnalisées, seules vos sélections s'appliqueront (plus les permissions du rôle)
                            </p>
                        </div>

                        @foreach($permissions as $group => $groupPermissions)
                            <div class="mb-6">
                                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-3 capitalize border-b pb-2">
                                    {{ ucfirst($group) }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($groupPermissions as $permission)
                                        @php
                                            $isRolePermission = in_array($permission->id, $rolePermissions);
                                            $isUserPermission = in_array($permission->id, $userDirectPermissions);
                                            // Cocher si l'utilisateur a la permission (via rôle OU directement)
                                            $hasPermission = $isRolePermission || $isUserPermission;
                                        @endphp
                                        <label class="flex items-center p-3 rounded-lg border cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700
                                            {{ $isUserPermission ? 'bg-blue-50 border-blue-400 dark:bg-blue-900/20 dark:border-blue-600' : '' }}
                                            {{ !$isUserPermission && $isRolePermission ? 'bg-green-50 border-green-300 dark:bg-green-900/20 dark:border-green-700' : '' }}
                                            {{ !$isUserPermission && !$isRolePermission ? 'border-gray-200 dark:border-gray-600' : '' }}">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->id }}"
                                                {{ $hasPermission ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                            >
                                            <span class="ml-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $permission->name }}
                                                @if($isRolePermission && !$isUserPermission)
                                                    <span class="ml-1 text-xs text-green-600 dark:text-green-400">(rôle)</span>
                                                @endif
                                                @if($isUserPermission)
                                                    <span class="ml-1 text-xs text-blue-600 dark:text-blue-400">(personnalisée)</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('users.show', $user) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        💾 Enregistrer les Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
