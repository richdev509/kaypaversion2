<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.credit.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Alertes Crédit Business</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif

            <!-- Filtres -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <select name="level" class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm px-3 py-2">
                        <option value="">Tous niveaux</option>
                        <option value="red" @selected(request('level') === 'red')>Critique</option>
                        <option value="orange" @selected(request('level') === 'orange')>Avertissement</option>
                        <option value="yellow" @selected(request('level') === 'yellow')>Attention</option>
                        <option value="default" @selected(request('level') === 'default')>Information</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">Filtrer</button>
                    @if(request('level'))
                        <a href="{{ route('business.credit.alerts') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg">Effacer</a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Niveau</th>
                            <th class="px-4 py-3 text-left">Business</th>
                            <th class="px-4 py-3 text-center">Jours sans flux</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($alerts as $alert)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'red'     => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
                                        'orange'  => 'bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300',
                                        'yellow'  => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
                                        'default' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 text-xs {{ $colors[$alert->level] ?? 'bg-gray-200 text-gray-600' }} rounded-full">
                                    {{ $alert->getLevelLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $alert->business->name }}</p>
                                <p class="text-xs font-mono text-gray-400">{{ $alert->business->business_number }}</p>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-gray-700 dark:text-gray-300">
                                {{ $alert->days_without_flux ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs {{ $alert->isOpen() ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300' }} rounded-full">
                                    {{ $alert->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $alert->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('business.credit.alert.show', $alert) }}"
                                       class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                        Détail →
                                    </a>
                                    @if($alert->isOpen())
                                    <form method="POST" action="{{ route('business.credit.alert.action', $alert) }}" class="flex gap-2">
                                        @csrf
                                        <select name="action" class="text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-2 py-1">
                                            <option value="appel">Appel</option>
                                            <option value="email">Email</option>
                                            <option value="visite">Visite</option>
                                            <option value="escalade">Escalader</option>
                                            <option value="resolution">Résoudre</option>
                                        </select>
                                        <input type="text" name="note" required placeholder="Note..."
                                            class="text-xs rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-2 py-1 w-32">
                                        <button type="submit" class="px-2 py-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded transition">OK</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune alerte ouverte.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($alerts->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $alerts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
