<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['account', 'client']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%");
                  });
            });
        }

        // Statistiques
        $stats = [
            'total' => WithdrawalRequest::count(),
            'pending' => WithdrawalRequest::where('status', 'pending')->count(),
            'processing' => WithdrawalRequest::where('status', 'processing')->count(),
            'completed' => WithdrawalRequest::where('status', 'completed')->count(),
            'rejected' => WithdrawalRequest::where('status', 'rejected')->count(),
            'total_amount_pending' => WithdrawalRequest::whereIn('status', ['pending', 'processing'])->sum('amount'),
            'total_amount_completed' => WithdrawalRequest::where('status', 'completed')->sum('amount'),
        ];

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.withdrawals.index', compact('requests', 'stats'));
    }

    public function show($id)
    {
        $request = WithdrawalRequest::with(['account', 'client'])->findOrFail($id);
        return view('admin.withdrawals.show', compact('request'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:processing,cancelled',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $withdrawalRequest = WithdrawalRequest::findOrFail($id);

        // Vérifier que le statut peut être changé
        if (!$withdrawalRequest->canBeProcessed()) {
            return back()->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        $updateData = [
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? $withdrawalRequest->admin_note,
        ];

        // Si la demande est annulée, enregistrer qui l'a annulée et quand
        if ($validated['status'] === 'cancelled') {
            $updateData['processed_by'] = auth()->id();
            $updateData['processed_at'] = now();
        }

        $withdrawalRequest->update($updateData);

        $message = $validated['status'] === 'processing'
            ? 'Demande mise en traitement.'
            : 'Demande annulée avec succès.';

        return redirect()->route('admin.withdrawals.show', $id)
            ->with('success', $message);
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $withdrawalRequest = WithdrawalRequest::findOrFail($id);

        // Vérifier que le statut permet l'approbation
        if (!$withdrawalRequest->canBeProcessed()) {
            return back()->with('error', 'Cette demande ne peut plus être approuvée.');
        }

        try {
            DB::beginTransaction();

            // Récupérer le compte
            $account = Account::where('account_id', $withdrawalRequest->account_id)->firstOrFail();

            // Utiliser amount_after comme solde réel du compte (ou balance si amount_after est null)
            $currentBalance = $account->amount_after ?? $account->balance ?? 0;

            // Vérifier le solde
            if ($currentBalance < $withdrawalRequest->amount) {
                throw new \Exception('Solde insuffisant sur le compte. Solde disponible: ' . number_format($currentBalance, 2) . ' HTG');
            }

            // Enregistrer les soldes avant/après
            $balanceBefore = $currentBalance;
            $balanceAfter = $balanceBefore - $withdrawalRequest->amount;

            // Déduire le montant du compte (mettre à jour amount_after)
            $account->amount_after = $balanceAfter;

            // Si le solde est à 0, passer le compte en statut "cloture"
            if ($balanceAfter <= 0) {
                $account->status = 'cloture';
            }

            $account->save();

            // Créer la transaction
            $transaction = AccountTransaction::create([
                'account_id' => $account->account_id,
                'client_id' => $withdrawalRequest->client->id,
                'type' => AccountTransaction::TYPE_RETRAIT,
                'amount' => $withdrawalRequest->amount,
                'amount_after' => $balanceAfter,
                'method' => $withdrawalRequest->method,
                'reference' => 'WR-' . $withdrawalRequest->id . '-' . time(),
                'note' => 'Retrait approuvé - ' . $withdrawalRequest->reference_id . ($validated['admin_note'] ? ' - ' . $validated['admin_note'] : ''),
                'status' => 'ACTIVE',
                'created_by' => auth()->id(),
            ]);

            // Mettre à jour la demande de retrait
            $withdrawalRequest->update([
                'status' => 'completed',
                'admin_note' => $validated['admin_note'] ?? null,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'transaction_id' => $transaction->id,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);

            DB::commit();

            Log::info('Demande de retrait approuvée', [
                'withdrawal_id' => $withdrawalRequest->id,
                'amount' => $withdrawalRequest->amount,
                'account_id' => $account->account_id,
                'processed_by' => auth()->id(),
            ]);

            return redirect()->route('admin.withdrawals.show', $id)
                ->with('success', 'Demande de retrait approuvée et montant déduit avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'approbation du retrait', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        $withdrawalRequest = WithdrawalRequest::findOrFail($id);

        // Vérifier que le statut permet le rejet
        if (!$withdrawalRequest->canBeProcessed()) {
            return back()->with('error', 'Cette demande ne peut plus être rejetée.');
        }

        $withdrawalRequest->update([
            'status' => 'rejected',
            'admin_note' => $validated['admin_note'],
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        Log::info('Demande de retrait rejetée', [
            'withdrawal_id' => $withdrawalRequest->id,
            'reason' => $validated['admin_note'],
            'processed_by' => auth()->id(),
        ]);

        return redirect()->route('admin.withdrawals.show', $id)
            ->with('success', 'Demande de retrait rejetée.');
    }
}
