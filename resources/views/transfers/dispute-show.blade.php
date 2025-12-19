<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                        Détails du Litige
                    </h1>
                    <a href="{{ route('transfers.disputes') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left"></i>
                        Retour
                    </a>
                </div>
            </div>

            <!-- Informations du transfert -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-exchange-alt text-blue-600"></i>
                    Informations du Transfert
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Numéro</label>
                        <p class="text-lg font-bold text-blue-600">{{ $transfer->transfer_number }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date de création</label>
                        <p class="text-lg text-gray-900">{{ $transfer->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Montant</label>
                        <p class="text-lg font-bold text-green-600">{{ number_format($transfer->amount, 2) }} GDS</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Statut du transfert</label>
                        <p>
                            @if($transfer->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock"></i> En attente
                                </span>
                            @elseif($transfer->status === 'paid')
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Payé
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle"></i> Annulé
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informations du litige -->
            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6 mb-6">
                <h2 class="text-xl font-bold text-red-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                    Informations du Litige
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-red-900">Statut du litige</label>
                        <div class="mt-1">
                            @if($transfer->dispute_status === 'pending')
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock"></i> En attente
                                </span>
                            @elseif($transfer->dispute_status === 'investigating')
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                    <i class="fas fa-search"></i> En investigation
                                </span>
                            @elseif($transfer->dispute_status === 'resolved')
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle"></i> Résolu
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle"></i> Rejeté
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-red-900">Déclaré par</label>
                        <p class="text-gray-900 mt-1">{{ $transfer->disputedBy->name ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-red-900">Date de déclaration</label>
                        <p class="text-gray-900 mt-1">{{ $transfer->disputed_at->format('d/m/Y à H:i') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-red-900">Motif du litige</label>
                        <div class="mt-1 p-4 bg-white border border-red-200 rounded-lg">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $transfer->dispute_reason }}</p>
                        </div>
                    </div>

                    @if($transfer->dispute_resolution)
                    <div>
                        <label class="text-sm font-medium text-red-900">Résolution</label>
                        <div class="mt-1 p-4 bg-white border border-red-200 rounded-lg">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $transfer->dispute_resolution }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-red-900">Résolu par</label>
                            <p class="text-gray-900 mt-1">{{ $transfer->resolvedBy->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-red-900">Date de résolution</label>
                            <p class="text-gray-900 mt-1">{{ $transfer->resolved_at ? $transfer->resolved_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Formulaire de mise à jour du statut -->
            @if(in_array($transfer->dispute_status, ['pending', 'investigating']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-edit text-blue-600"></i>
                    Mettre à Jour le Statut
                </h2>

                <form method="POST" action="{{ route('transfers.dispute.update', $transfer) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-list-alt text-blue-500 mr-1"></i>
                            Nouveau statut
                        </label>
                        <select name="dispute_status"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Sélectionner un statut</option>
                            <option value="pending" {{ $transfer->dispute_status === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="investigating" {{ $transfer->dispute_status === 'investigating' ? 'selected' : '' }}>En investigation</option>
                            <option value="resolved">Résolu</option>
                            <option value="rejected">Rejeté</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-comment-alt text-blue-500 mr-1"></i>
                            Commentaire / Résolution
                            <span class="text-xs text-gray-500">(requis si résolu ou rejeté)</span>
                        </label>
                        <textarea name="dispute_resolution"
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Expliquez la résolution ou le rejet du litige..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('transfers.disputes') }}"
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-2"></i>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Lien vers le transfert -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <a href="{{ route('transfers.show', $transfer) }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition">
                    <i class="fas fa-eye"></i>
                    Voir les Détails Complets du Transfert
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
