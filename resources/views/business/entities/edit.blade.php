<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.entities.show', $entity) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Modifier — {{ $entity->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $entity->business_number }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg text-sm text-red-700 dark:text-red-400">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('business.entities.update', $entity) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    {{-- Propriétaire (lecture seule) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Propriétaire</label>
                        <div class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 px-3 py-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ $entity->ownerClient?->first_name }} {{ $entity->ownerClient?->last_name }}
                            <span class="ml-2 font-mono text-xs text-gray-400">— non modifiable</span>
                        </div>
                    </div>

                    {{-- Nom commercial --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nom commercial <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $entity->name) }}"
                            required maxlength="255"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Raison sociale --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Raison sociale</label>
                        <input type="text" name="legal_name" value="{{ old('legal_name', $entity->legal_name !== $entity->name ? $entity->legal_name : '') }}"
                            maxlength="255"
                            placeholder="Optionnel — nom légal si différent du nom commercial"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Profil crédit --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Profil crédit <span class="text-red-500">*</span>
                        </label>
                        <select name="profile" required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="standard" @selected(old('profile', $entity->profile) === 'standard')>Standard</option>
                            <option value="etabli"   @selected(old('profile', $entity->profile) === 'etabli')>Établi</option>
                            <option value="premium"  @selected(old('profile', $entity->profile) === 'premium')>Premium</option>
                        </select>
                    </div>

                    {{-- Adresse --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                        <input type="text" name="address" value="{{ old('address', $entity->address) }}"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Ville --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville</label>
                        <input type="text" name="city" value="{{ old('city', $entity->city) }}" maxlength="100"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Téléphone + Email --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $entity->phone) }}" maxlength="20"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $entity->email) }}"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- RCCM + NIF --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RCCM</label>
                            <input type="text" name="rccm" value="{{ old('rccm', $entity->rccm) }}" maxlength="100"
                                placeholder="N° registre de commerce"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIF</label>
                            <input type="text" name="nif" value="{{ old('nif', $entity->nif) }}" maxlength="50"
                                placeholder="N° identifiant fiscal"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('business.entities.show', $entity) }}"
                           class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Annuler
                        </a>
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
