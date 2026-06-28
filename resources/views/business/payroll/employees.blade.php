<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.payroll.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Employés Business</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Nom ou N° compte..."
                        class="flex-1 min-w-48 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                    <select name="business_id" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                        <option value="">Tous les business</option>
                        @foreach($businesses as $biz)
                            <option value="{{ $biz->id }}" @selected(request('business_id') == $biz->id)>
                                {{ $biz->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">Filtrer</button>
                    @if(request()->hasAny(['search', 'business_id']))
                        <a href="{{ route('business.payroll.employees') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg">Effacer</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nom</th>
                            <th class="px-4 py-3 text-left">Business</th>
                            <th class="px-4 py-3 text-left">Poste</th>
                            <th class="px-4 py-3 text-left">Compte KAYPA</th>
                            <th class="px-4 py-3 text-right">Salaire défaut</th>
                            <th class="px-4 py-3 text-center">Actif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($employees as $emp)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $emp->full_name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $emp->business->name }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $emp->poste ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-indigo-600 dark:text-indigo-400">
                                {{ $emp->kaypa_account_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                {{ $emp->salaire_defaut ? number_format($emp->salaire_defaut, 2) . ' HTG' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($emp->isActive())
                                    <span class="text-green-600 dark:text-green-400">✓</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun employé trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($employees->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $employees->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
