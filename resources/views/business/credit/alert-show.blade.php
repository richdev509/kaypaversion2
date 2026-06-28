<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('business.credit.alerts') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Alerte Crédit — {{ $alert->business->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $alert->business->business_number }}</p>
                </div>
            </div>
            @php
                $levelColors = [
                    'red'     => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
                    'orange'  => 'bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300',
                    'yellow'  => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
                    'default' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
                ];
            @endphp
            <span class="px-3 py-1 text-sm font-medium {{ $levelColors[$alert->level] ?? 'bg-gray-200 text-gray-600' }} rounded-full">
                {{ $alert->getLevelLabel() }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">{{ session('error') }}</div>
            @endif

            {{-- Infos alerte --}}
            <div class="grid sm:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-3">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Détails de l'alerte</h3>
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                            <dd>
                                @php
                                    $statusColors = [
                                        'open'      => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
                                        'contacted' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
                                        'escalated' => 'bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300',
                                        'resolved'  => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 text-xs {{ $statusColors[$alert->status] ?? 'bg-gray-200 text-gray-600' }} rounded-full">
                                    {{ $alert->getStatusLabel() }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Jours sans flux</dt>
                            <dd class="font-bold text-gray-900 dark:text-gray-100">{{ $alert->days_without_flux ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Créée le</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $alert->created_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($alert->resolved_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Résolue le</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $alert->resolved_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Résolue par</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $alert->resolvedBy?->name ?? '—' }}</dd>
                        </div>
                        @endif
                        @if($alert->note)
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400 mb-1">Note</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $alert->note }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 space-y-3">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Crédit associé</h3>
                    @if($alert->credit)
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Limite approuvée</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($alert->credit->approved_limit, 2) }} HTG</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Utilisé</dt>
                            <dd class="font-medium text-orange-600 dark:text-orange-400">{{ number_format($alert->credit->credit_used, 2) }} HTG</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Disponible</dt>
                            <dd class="font-medium text-green-600 dark:text-green-400">{{ number_format($alert->credit->getAvailableCredit(), 2) }} HTG</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Taux mensuel</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $alert->credit->getEffectiveTaux() }}%</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Expiration</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $alert->credit->expires_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('business.credit.show', $alert->credit) }}"
                           class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            Voir le dossier crédit complet →
                        </a>
                    </div>
                    @else
                        <p class="text-sm text-gray-400">Crédit non trouvé.</p>
                    @endif
                </div>
            </div>

            {{-- Formulaire nouvelle action (si alerte non résolue) --}}
            @if($alert->status !== 'resolved')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Enregistrer une action</h3>
                @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('comptable'))
                <form method="POST" action="{{ route('business.credit.alert.action', $alert) }}" class="space-y-4">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Type d'action <span class="text-red-500">*</span>
                            </label>
                            <select name="action" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="appel">Appel téléphonique</option>
                                <option value="email">Email</option>
                                <option value="visite">Visite en agence</option>
                                <option value="escalade">Escalader</option>
                                <option value="resolution">Marquer comme résolu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Note <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="note" required maxlength="1000"
                                placeholder="Détails de l'action effectuée..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            Enregistrer
                        </button>
                    </div>
                </form>
                @else
                    <p class="text-sm text-gray-400">Vous n'avez pas les droits pour enregistrer une action.</p>
                @endif
            </div>
            @endif

            {{-- Historique des actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">Historique des actions</h3>
                    <span class="text-sm text-gray-400">{{ $alert->actionLogs->count() }} action(s)</span>
                </div>

                @if($alert->actionLogs->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-400 text-sm">Aucune action enregistrée.</div>
                @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($alert->actionLogs as $log)
                    @php
                        $actionIcons = [
                            'appel'      => '📞',
                            'email'      => '✉️',
                            'visite'     => '🏢',
                            'escalade'   => '⬆️',
                            'resolution' => '✅',
                            'note'       => '📝',
                            'approbation'=> '✔️',
                            'rejet'      => '❌',
                        ];
                        $actionLabels = [
                            'appel'       => 'Appel',
                            'email'       => 'Email',
                            'visite'      => 'Visite',
                            'escalade'    => 'Escalade',
                            'resolution'  => 'Résolution',
                            'note'        => 'Note',
                            'approbation' => 'Approbation',
                            'rejet'       => 'Rejet',
                        ];
                    @endphp
                    <div class="px-6 py-4 flex items-start gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center text-base">
                            {{ $actionIcons[$log->action] ?? '•' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $actionLabels[$log->action] ?? $log->action }}
                                </span>
                                <span class="text-xs text-gray-400 flex-shrink-0">
                                    {{ $log->created_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $log->note }}</p>
                            <p class="text-xs text-gray-400 mt-1">Par {{ $log->doneBy?->name ?? '—' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
