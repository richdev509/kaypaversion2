<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Programmes Scolaires</h2>
            @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('school-programs.create') }}"
               class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                + Nouveau programme
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- KPI --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total programmes</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actifs</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['actifs'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total inscrits</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $stats['inscrits'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Coupons actifs</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $stats['coupons_actifs'] }}</p>
                </div>
            </div>

            {{-- Filtre --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <form method="GET" class="flex gap-3 items-end">
                    <select name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                        <option value="">Tous statuts</option>
                        <option value="actif" @selected(request('status') === 'actif')>Actif</option>
                        <option value="archive" @selected(request('status') === 'archive')>Archivé</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">Filtrer</button>
                    @if(request('status'))
                        <a href="{{ route('school-programs.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg">Effacer</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nom</th>
                            <th class="px-4 py-3 text-left">Inscriptions</th>
                            <th class="px-4 py-3 text-left">Période coupon</th>
                            <th class="px-4 py-3 text-center">Inscrits</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($programs as $program)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $program->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $program->inscription_debut->format('d/m/Y') }} → {{ $program->inscription_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $program->date_debut->format('d/m/Y') }} → {{ $program->date_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $program->enrollments_count }}
                            </td>
                            <td class="px-4 py-3">
                                @if($program->isActive())
                                    <span class="px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full">Actif</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-full">Archivé</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 flex gap-3">
                                <a href="{{ route('school-programs.show', $program) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Voir</a>
                                @if(auth()->user()->hasRole('admin'))
                                <a href="{{ route('school-programs.edit', $program) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Modifier</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun programme trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($programs->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $programs->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
