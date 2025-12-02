<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ➕ {{ __('Créer un Rôle') }}
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <!-- Nom du rôle -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom du Rôle <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Ex: Superviseur, Auditeur, etc."
                                   required>
                            <p class="mt-1 text-sm text-gray-500">
                                Utilisez un nom descriptif (minuscules recommandées)
                            </p>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Permissions Accordées
                            </label>
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
                                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Cochez les permissions que ce rôle aura. Vous pourrez les modifier plus tard.
                            </p>
                        </div>

                        <!-- Boutons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Annuler
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                ✅ Créer le Rôle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
