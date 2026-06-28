<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('business.credit.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Plans de Taux Crédit</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nom</th>
                            <th class="px-4 py-3 text-left">Profil</th>
                            <th class="px-4 py-3 text-center">Durée (mois)</th>
                            <th class="px-4 py-3 text-right">Taux mensuel</th>
                            <th class="px-4 py-3 text-right">Taux pénalité</th>
                            <th class="px-4 py-3 text-left">Valide du</th>
                            <th class="px-4 py-3 text-left">Au</th>
                            <th class="px-4 py-3 text-center">Actif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($plans as $plan)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs {{ $plan->profile === 'premium' ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300' : ($plan->profile === 'etabli' ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400') }} rounded-full">
                                    {{ $plan->getProfileLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                                {{ $plan->duration_min_months }} – {{ $plan->duration_max_months }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-indigo-600 dark:text-indigo-400">{{ $plan->taux_mensuel }}%</td>
                            <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">{{ $plan->taux_penalite }}%</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $plan->effective_from?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $plan->effective_to?->format('d/m/Y') ?? 'Illimité' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($plan->is_active)
                                    <span class="text-green-600 dark:text-green-400 font-bold">✓</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucun plan de taux défini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
