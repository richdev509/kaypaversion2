<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ✏️ {{ __('Éditer le Rôle: ') }} <span class="text-blue-600">{{ $role->name }}</span>
            </h2>
            <a href="{{ route('admin.roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Erreurs de validation:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($role->name === 'admin')
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">⚠️ Attention:</strong>
                    <span class="block sm:inline">Le rôle 'admin' est protégé et ne peut pas être modifié.</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nom du rôle -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom du Rôle <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   {{ $role->name === 'admin' ? 'readonly' : '' }}
                                   required>
                            @if($role->name === 'admin')
                                <p class="mt-1 text-sm text-gray-500">
                                    Le nom du rôle admin ne peut pas être modifié
                                </p>
                            @endif
                        </div>

                        <!-- Statistiques -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-600">Utilisateurs assignés:</span>
                                    <span class="text-lg font-bold text-gray-900">{{ $role->users()->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Permissions actuelles:</span>
                                    <span class="text-lg font-bold text-gray-900">{{ count($rolePermissions) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Permissions Accordées
                            </label>
                            <div class="mb-3 flex space-x-2">
                                <button type="button" onclick="checkAll()" class="text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 py-1 px-3 rounded">
                                    ✅ Tout cocher
                                </button>
                                <button type="button" onclick="uncheckAll()" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-800 py-1 px-3 rounded">
                                    ❌ Tout décocher
                                </button>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg max-h-96 overflow-y-auto">
                                @foreach($permissions as $group => $perms)
                                    <div class="mb-4 border-b border-gray-200 pb-3">
                                        <h4 class="font-semibold text-gray-800 mb-2 capitalize">
                                            📁 {{ str_replace('-', ' ', $group) }}
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 ml-4">
                                            @foreach($perms as $permission)
                                                <label class="flex items-center space-x-2">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                           class="permission-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annuler
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                💾 Enregistrer les Modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkAll() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        function uncheckAll() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    </script>
</x-app-layout>
