<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                            Gestion des Litiges
                        </h1>
                        <p class="text-gray-600 mt-2">Suivi et résolution des transferts contestés</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('transfers.all') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            <i class="fas fa-arrow-left"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" action="{{ route('transfers.disputes') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Date de début -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-red-500 mr-1"></i>
                                Date de début
                            </label>
                            <input type="date"
                                   name="date_debut"
                                   value="{{ $dateDebut }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        </div>

                        <!-- Date de fin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-red-500 mr-1"></i>
                                Date de fin
                            </label>
                            <input type="date"
                                   name="date_fin"
                                   value="{{ $dateFin }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        </div>

                        <!-- Statut du litige -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter text-red-500 mr-1"></i>
                                Statut du litige
                            </label>
                            <select name="dispute_status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ $disputeStatus === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="investigating" {{ $disputeStatus === 'investigating' ? 'selected' : '' }}>En investigation</option>
                                <option value="resolved" {{ $disputeStatus === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                <option value="rejected" {{ $disputeStatus === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-search"></i>
                            Filtrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-100 text-sm font-medium">Total</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['total']) }}</p>
                        </div>
                        <i class="fas fa-list text-3xl text-white/40"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">En Attente</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['pending']) }}</p>
                        </div>
                        <i class="fas fa-clock text-3xl text-white/40"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Investigation</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['investigating']) }}</p>
                        </div>
                        <i class="fas fa-search text-3xl text-white/40"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Résolus</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['resolved']) }}</p>
                        </div>
                        <i class="fas fa-check-circle text-3xl text-white/40"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm font-medium">Rejetés</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['rejected']) }}</p>
                        </div>
                        <i class="fas fa-times-circle text-3xl text-white/40"></i>
                    </div>
                </div>
            </div>

            <!-- Liste des litiges -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list text-red-600 mr-2"></i>
                        Litiges ({{ $disputes->total() }})
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfert</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Litige</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Déclaré par</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motif</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($disputes as $dispute)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('transfers.disputes.show', $dispute) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $dispute->transfer_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $dispute->disputed_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $dispute->disputedBy->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="max-w-xs truncate" title="{{ $dispute->dispute_reason }}">
                                            {{ Str::limit($dispute->dispute_reason, 50) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($dispute->dispute_status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock"></i> En attente
                                            </span>
                                        @elseif($dispute->dispute_status === 'investigating')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                <i class="fas fa-search"></i> Investigation
                                            </span>
                                        @elseif($dispute->dispute_status === 'resolved')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle"></i> Résolu
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle"></i> Rejeté
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ number_format($dispute->amount, 2) }} GDS
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('transfers.disputes.show', $dispute) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            <i class="fas fa-eye"></i>
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500 text-lg">Aucun litige trouvé pour cette période</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($disputes->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $disputes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
