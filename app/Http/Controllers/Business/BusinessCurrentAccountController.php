<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessCurrentAccount;
use App\Services\Business\BusinessAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessCurrentAccountController extends Controller
{
    public function __construct(private BusinessAccountService $service) {}

    public function show(BusinessCurrentAccount $account)
    {
        $account->load('business');

        $transactions = $account->transactions()
            ->with('creator')
            ->latest()
            ->paginate(25);

        return view('business.current-accounts.show', compact('account', 'transactions'));
    }

    public function depositForm(BusinessCurrentAccount $account)
    {
        $account->load('business');
        return view('business.current-accounts.deposit', compact('account'));
    }

    public function deposit(Request $request, BusinessCurrentAccount $account)
    {
        $validated = $request->validate([
            'amount'    => 'required|numeric|min:1',
            'method'    => 'required|string|max:50',
            'reference' => 'nullable|string|max:100',
            'note'      => 'nullable|string|max:500',
        ]);

        try {
            $this->service->deposit(
                $account,
                (float) $validated['amount'],
                $validated['method'],
                $validated['reference'] ?? null,
                $validated['note'] ?? null,
                Auth::id()
            );

            return redirect()
                ->route('business.kcb.show', $account)
                ->with('success', 'Dépôt effectué avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function withdrawForm(BusinessCurrentAccount $account)
    {
        $account->load('business');
        return view('business.current-accounts.withdraw', compact('account'));
    }

    public function withdraw(Request $request, BusinessCurrentAccount $account)
    {
        $validated = $request->validate([
            'amount'    => 'required|numeric|min:1',
            'method'    => 'required|string|max:50',
            'reference' => 'nullable|string|max:100',
            'note'      => 'nullable|string|max:500',
        ]);

        try {
            $this->service->withdraw(
                $account,
                (float) $validated['amount'],
                $validated['method'],
                $validated['reference'] ?? null,
                $validated['note'] ?? null,
                Auth::id()
            );

            return redirect()
                ->route('business.kcb.show', $account)
                ->with('success', 'Retrait effectué avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
