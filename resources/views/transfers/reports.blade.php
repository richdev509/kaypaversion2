<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                            <i class="fas fa-file-chart-line text-blue-600"></i>
                            Rapports de Transfert
                        </h1>
                        <p class="text-gray-600 mt-2">Génération de rapports Excel et PDF</p>
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
                <form method="GET" action="{{ route('transfers.reports') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Date de début -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                                Date de début
                            </label>
                            <input type="date"
                                   name="date_debut"
                                   value="{{ $dateDebut }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Date de fin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                                Date de fin
                            </label>
                            <input type="date"
                                   name="date_fin"
                                   value="{{ $dateFin }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Statut -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-filter text-blue-500 mr-1"></i>
                                Statut
                            </label>
                            <select name="status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Payé</option>
                                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>

                        <!-- Branche -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-code-branch text-blue-500 mr-1"></i>
                                Branche
                            </label>
                            <select name="branch_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Toutes les branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-search"></i>
                            Filtrer
                        </button>

                        <div class="flex gap-2">
                            <a href="{{ route('transfers.reports.excel', request()->all()) }}"
                               class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-file-csv"></i>
                                Exporter CSV
                            </a>
                            <a href="{{ route('transfers.reports.pdf', request()->all()) }}"
                               class="inline-flex items-center gap-2 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-file-pdf"></i>
                                Exporter PDF
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Transferts</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_count']) }}</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-4">
                            <i class="fas fa-exchange-alt text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Montant Total</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_amount'], 2) }} GDS</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-4">
                            <i class="fas fa-money-bill-wave text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Frais Collectés</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_fees'], 2) }} GDS</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-4">
                            <i class="fas fa-coins text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Revenu Total</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_revenue'], 2) }} GDS</p>
                        </div>
                        <div class="bg-white/20 rounded-full p-4">
                            <i class="fas fa-chart-line text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statuts -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">En Attente</p>
                            <p class="text-2xl font-bold text-yellow-600 mt-2">{{ number_format($stats['pending_count']) }}</p>
                        </div>
                        <i class="fas fa-clock text-4xl text-yellow-500"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Payés</p>
                            <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($stats['paid_count']) }}</p>
                        </div>
                        <i class="fas fa-check-circle text-4xl text-green-500"></i>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Annulés</p>
                            <p class="text-2xl font-bold text-red-600 mt-2">{{ number_format($stats['cancelled_count']) }}</p>
                        </div>
                        <i class="fas fa-times-circle text-4xl text-red-500"></i>
                    </div>
                </div>
            </div>

            <!-- Statistiques par Branche -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-500 to-indigo-600">
                    <h2 class="text-lg font-semibold text-white">
                        <i class="fas fa-code-branch mr-2"></i>
                        Statistiques par Branche
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" rowspan="2">Branche</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50" colspan="3">Transferts Envoyés</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50" colspan="2">Transferts Payés</th>
                            </tr>
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">Nombre</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">Montant</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">Frais</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50">Nombre</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($branchStats as $stat)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <i class="fas fa-building text-indigo-600 mr-2"></i>
                                        {{ $stat['branch']->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-blue-600 bg-blue-50">
                                        {{ number_format($stat['sent_count']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-blue-600 bg-blue-50">
                                        {{ number_format($stat['sent_amount'], 2) }} GDS
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-purple-600 bg-blue-50">
                                        {{ number_format($stat['sent_fees'], 2) }} GDS
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-green-600 bg-green-50">
                                        {{ number_format($stat['paid_count']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-green-600 bg-green-50">
                                        {{ number_format($stat['paid_amount'], 2) }} GDS
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Liste des transferts -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list text-blue-600 mr-2"></i>
                        Liste des Transferts ({{ $transfers->total() }})
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expéditeur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bénéficiaire</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frais</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branche</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($transfers as $transfer)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('transfers.show', $transfer) }}"
                                           class="text-blue-600 hover:text-blue-800 font-medium">
                                            {{ $transfer->transfer_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $transfer->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transfer->sender_name }}<br>
                                        <span class="text-gray-500">{{ $transfer->sender_country_code }} {{ $transfer->sender_phone }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transfer->receiver_name }}<br>
                                        <span class="text-gray-500">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ number_format($transfer->amount, 2) }} GDS
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ number_format($transfer->fees, 2) }} GDS
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $transfer->branch->name ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500 text-lg">Aucun transfert trouvé pour cette période</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($transfers->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $transfers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
