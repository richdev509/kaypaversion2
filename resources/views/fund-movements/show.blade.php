<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📋 Détails du mouvement: {{ $fundMovement->reference }}
            </h2>
            <a href="{{ route('fund-movements.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                ← Retour
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Informations principales -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Informations générales
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Référence</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $fundMovement->reference }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Type</p>
                            <p class="mt-1">{!! $fundMovement->type_badge !!}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Montant</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($fundMovement->amount, 2) }} HTG</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Statut</p>
                            <p class="mt-1">{!! $fundMovement->status_badge !!}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Type de source</p>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $fundMovement->source_type }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Date de création</p>
                            <p class="text-gray-900 dark:text-gray-100">{{ $fundMovement->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Source et Destination -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Source et Destination
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">🏢 Source</p>
                            @if($fundMovement->source_type === 'SUCCURSALE')
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $fundMovement->sourceBranch->name ?? 'N/A' }}
                                </p>
                                @if($fundMovement->sourceBranch)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $fundMovement->sourceBranch->address }}</p>
                                @endif
                            @else
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $fundMovement->external_source ?? $fundMovement->source_type }}
                                </p>
                            @endif
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">🎯 Destination</p>
                            @if($fundMovement->destinationBranch)
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $fundMovement->destinationBranch->name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $fundMovement->destinationBranch->address }}</p>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">N/A</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Raison et Notes -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Détails
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Raison du mouvement</p>
                            <p class="text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900 p-3 rounded">
                                {{ $fundMovement->reason }}
                            </p>
                        </div>

                        @if($fundMovement->notes)
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Notes additionnelles</p>
                            <p class="text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900 p-3 rounded">
                                {{ $fundMovement->notes }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations de validation -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Validation
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Créé par</p>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $fundMovement->creator->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $fundMovement->created_at->format('d/m/Y à H:i') }}</p>
                        </div>

                        @if($fundMovement->approved_by)
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $fundMovement->status === 'APPROVED' ? 'Approuvé' : 'Rejeté' }} par
                            </p>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $fundMovement->approver->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $fundMovement->approved_at?->format('d/m/Y à H:i') }}</p>
                        </div>
                        @endif
                    </div>

                    @if($fundMovement->rejection_reason)
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">Raison du rejet :</p>
                        <p class="text-red-700 dark:text-red-400">{{ $fundMovement->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($fundMovement->isPending() && Auth::user()->hasPermissionTo('fund-movements.approve'))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Actions
                    </h3>

                    <div class="flex space-x-4">
                        <!-- Approuver -->
                        <form action="{{ route('fund-movements.approve', $fundMovement) }}" method="POST"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir approuver ce mouvement de fonds ?');">
                            @csrf
                            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                ✅ Approuver
                            </button>
                        </form>

                        <!-- Rejeter -->
                        <button onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                            ❌ Rejeter
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal de rejet -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Rejeter le mouvement</h3>
                <form action="{{ route('fund-movements.reject', $fundMovement) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Raison du rejet <span class="text-red-500">*</span>
                        </label>
                        <textarea name="rejection_reason" rows="4" required
                                  class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                  placeholder="Expliquez pourquoi ce mouvement est rejeté..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                            Confirmer le rejet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
