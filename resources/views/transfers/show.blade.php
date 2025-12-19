<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                💸 Transfert {{ $transfer->transfer_number }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('transfers.receipt-sender', $transfer) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition" target="_blank">
                    <i class="fas fa-print"></i> Imprimer Reçu
                </a>
                @if($transfer->status === 'pending' && $transfer->branch_id === Auth::user()->branch_id)
                    <a href="{{ route('transfers.edit', $transfer) }}" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                @endif
                @if($transfer->status === 'pending')
                    <a href="{{ route('transfers.pay', $transfer) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-hand-holding-usd"></i> Payer
                    </a>
                @endif
                @if($transfer->status === 'paid')
                    <a href="{{ route('transfers.receipt-receiver', $transfer) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition" target="_blank">
                        <i class="fas fa-receipt"></i> Reçu Paiement
                    </a>
                @endif
                @if(!$transfer->is_disputed && in_array($transfer->status, ['pending', 'paid']))
                    <button type="button" onclick="document.getElementById('dispute-modal').classList.remove('hidden')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-exclamation-triangle"></i> Signaler un Litige
                    </button>
                @endif
                @if($transfer->is_disputed)
                    <a href="{{ route('transfers.disputes.show', $transfer) }}" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-exclamation-circle"></i> Voir Litige
                    </a>
                @endif
                <a href="{{ route('transfers.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
        <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
        @endif

        @if($transfer->is_disputed)
        <div class="mb-4 bg-red-100 dark:bg-red-900 border-2 border-red-400 dark:border-red-600 text-red-800 dark:text-red-200 px-6 py-4 rounded-lg shadow-lg">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-2xl mt-1"></i>
                <div>
                    <h4 class="font-bold text-lg mb-1">⚠️ CE TRANSFERT EST EN LITIGE</h4>
                    <p class="mb-2">Statut:
                        @if($transfer->dispute_status === 'pending')
                            <span class="font-semibold">En attente de traitement</span>
                        @elseif($transfer->dispute_status === 'investigating')
                            <span class="font-semibold">En cours d'investigation</span>
                        @elseif($transfer->dispute_status === 'resolved')
                            <span class="font-semibold text-green-700">Résolu</span>
                        @else
                            <span class="font-semibold">Rejeté</span>
                        @endif
                    </p>
                    <a href="{{ route('transfers.disputes.show', $transfer) }}" class="inline-flex items-center gap-2 text-red-700 dark:text-red-300 font-semibold hover:underline">
                        Voir les détails du litige <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div class="space-y-6">
            <!-- Informations générales du transfert -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-500 to-blue-600">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-info-circle"></i> Informations du Transfert
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Numéro de Transfert</label>
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $transfer->transfer_number }}</div>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Statut</label>
                                <div>{!! $transfer->status_badge !!}</div>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Date de Création</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->created_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            @if($transfer->paid_at)
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Date de Paiement</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->paid_at->format('d/m/Y à H:i') }}</div>
                            </div>
                            @endif
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>

                        <h4 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">
                            <i class="fas fa-user"></i> Expéditeur
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Nom Complet</label>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $transfer->sender_name }}</div>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Téléphone</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->sender_country_code }} {{ $transfer->sender_phone }}</div>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">NINU</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->sender_ninu }}</div>
                            </div>
                            @if($transfer->sender_account_id)
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Compte Kaypa</label>
                                <div>
                                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-sm">{{ $transfer->sender_account_id }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">(Réduction appliquée)</span>
                                </div>
                            </div>
                            @endif
                            @if($transfer->sender_address)
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-500 dark:text-gray-400">Adresse</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->sender_address }}</div>
                                @if($transfer->senderCity)
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transfer->senderCity->name }}, {{ $transfer->senderCommune->name }}, {{ $transfer->senderDepartment->name }}
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>

                        <h4 class="text-lg font-semibold text-green-600 dark:text-green-400 mb-4">
                            <i class="fas fa-user-check"></i> Bénéficiaire
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Nom Complet</label>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $transfer->receiver_name }}</div>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">Téléphone</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->receiver_country_code }} {{ $transfer->receiver_phone }}</div>
                            </div>
                            @if($transfer->receiver_ninu)
                            <div>
                                <label class="text-sm text-gray-500 dark:text-gray-400">NINU (vérifié au paiement)</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->receiver_ninu }}</div>
                            </div>
                            @endif
                            @if($transfer->receiver_address)
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-500 dark:text-gray-400">Adresse</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ $transfer->receiver_address }}</div>
                                @if($transfer->receiverCity)
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transfer->receiverCity->name }}, {{ $transfer->receiverCommune->name }}, {{ $transfer->receiverDepartment->name }}
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        @if($transfer->note)
                        <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Note</label>
                            <div class="text-gray-900 dark:text-gray-100 mt-1">{{ $transfer->note }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Montants et Traçabilité en bas, côte à côte -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6 mb-6">
                <!-- Montants -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-500 to-yellow-600">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-dollar-sign"></i> Montants
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Montant:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($transfer->amount, 0) }} GDS</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Frais:</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($transfer->fees, 0) }} GDS</span>
                            </div>
                            @if($transfer->discount > 0)
                            <div class="flex justify-between text-green-600 dark:text-green-400">
                                <span>Réduction:</span>
                                <span class="font-semibold">-{{ number_format($transfer->discount, 0) }} GDS</span>
                            </div>
                            @endif
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                                <div class="flex justify-between text-lg">
                                    <span class="font-bold text-gray-900 dark:text-gray-100">TOTAL:</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ number_format($transfer->total_amount, 0) }} GDS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Traçabilité -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-500 to-purple-600">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-history"></i> Traçabilité
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="text-sm text-gray-500 dark:text-gray-400">Créé par</label>
                            <div class="text-gray-900 dark:text-gray-100 font-semibold">{{ $transfer->createdBy->name ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <div>
                            <label class="text-sm text-gray-500 dark:text-gray-400">Branche d'envoi</label>
                            <div class="text-gray-900 dark:text-gray-100">{{ $transfer->branch->name ?? 'N/A' }}</div>
                        </div>

                        @if($transfer->paid_by)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4"></div>
                        <div>
                            <label class="text-sm text-gray-500 dark:text-gray-400">Payé par</label>
                            <div class="text-gray-900 dark:text-gray-100 font-semibold">{{ $transfer->paidBy->name ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $transfer->paid_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <div>
                            <label class="text-sm text-gray-500 dark:text-gray-400">Branche de paiement</label>
                            <div class="text-gray-900 dark:text-gray-100">{{ $transfer->paidAtBranch->name ?? 'N/A' }}</div>
                        </div>
                        @endif

                        @if($transfer->modification_history)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4"></div>
                        <div>
                            <label class="text-sm font-semibold text-orange-600 dark:text-orange-400 mb-2 block">
                                <i class="fas fa-history"></i> Historique des Modifications
                            </label>
                            @php
                                $history = json_decode($transfer->modification_history, true) ?? [];
                            @endphp
                            <div class="space-y-2">
                                @foreach($history as $entry)
                                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-3">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $entry['modified_by'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($entry['modified_at'])->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    @if(!empty($entry['changes']))
                                    <div class="space-y-1 text-xs">
                                        @foreach($entry['changes'] as $field => $change)
                                        <div class="text-gray-700 dark:text-gray-300">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong>
                                            <span class="line-through text-red-600">{{ $change['old'] }}</span>
                                            →
                                            <span class="text-green-600">{{ $change['new'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions (si en attente) -->
            @if($transfer->status === 'pending')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-2 border-yellow-400 dark:border-yellow-600">
                <div class="p-6 text-center space-y-3">
                    <p class="font-semibold text-gray-900 dark:text-gray-100">Ce transfert est en attente de paiement</p>
                    <a href="{{ route('transfers.pay', $transfer) }}" class="block w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                        <i class="fas fa-hand-holding-usd"></i> Payer Maintenant
                    </a>
                    @can('cancel', $transfer)
                    <button type="button" onclick="document.getElementById('cancelModal').classList.remove('hidden')" class="block w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-times"></i> Annuler le Transfert
                    </button>
                    @endcan
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal d'annulation -->
@if($transfer->status === 'pending')
<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <form method="POST" action="{{ route('transfers.cancel', $transfer) }}">
            @csrf
            <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">
                    <i class="fas fa-exclamation-triangle"></i> Annuler le Transfert
                </h3>
            </div>

            <div class="mb-4">
                <p class="text-gray-700 dark:text-gray-300 mb-4">Êtes-vous sûr de vouloir annuler ce transfert?</p>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Raison de l'annulation <span class="text-red-500">*</span>
                </label>
                <textarea name="cancellation_reason" rows="3" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')"
                    class="flex-1 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Fermer
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    Confirmer l'Annulation
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal Signaler un Litige -->
<div id="dispute-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white dark:bg-gray-800">
        <form method="POST" action="{{ route('transfers.dispute.create', $transfer) }}">
            @csrf
            <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">
                    <i class="fas fa-exclamation-triangle"></i> Signaler un Litige
                </h3>
            </div>

            <div class="mb-4">
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    Décrivez en détail le problème rencontré avec ce transfert. Un litige sera créé et sera traité par l'équipe de gestion.
                </p>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Motif du litige <span class="text-red-500">*</span>
                </label>
                <textarea name="dispute_reason" rows="5" required
                    placeholder="Décrivez le problème en détail (minimum 10 caractères)..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white"></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 caractères requis</p>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('dispute-modal').classList.add('hidden')"
                    class="flex-1 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Signaler le Litige
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
