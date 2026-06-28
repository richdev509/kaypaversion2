<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessSavingsAccount;
use App\Services\Business\BusinessAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessSavingsAccountController extends Controller
{
    public function __construct(private BusinessAccountService $service) {}

    public function show(BusinessSavingsAccount $account)
    {
        $account->load('business', 'creator');
        return view('business.savings-accounts.show', compact('account'));
    }

    public function depositForm(BusinessSavingsAccount $account)
    {
        $account->load('business');
        return view('business.savings-accounts.deposit', compact('account'));
    }

    public function deposit(Request $request, BusinessSavingsAccount $account)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'nullable|string|max:500',
        ]);

        try {
            $this->service->depositToSavings(
                $account,
                (float) $validated['amount'],
                $validated['note'] ?? null,
                Auth::id()
            );

            return redirect()
                ->route('business.keb.show', $account)
                ->with('success', 'Dépôt épargne effectué avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function withdrawForm(BusinessSavingsAccount $account)
    {
        $account->load('business');
        return view('business.savings-accounts.withdraw', compact('account'));
    }

    public function withdraw(Request $request, BusinessSavingsAccount $account)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'nullable|string|max:500',
        ]);

        try {
            $this->service->withdrawFromSavings(
                $account,
                (float) $validated['amount'],
                $validated['note'] ?? null,
                Auth::id()
            );

            return redirect()
                ->route('business.keb.show', $account)
                ->with('success', 'Retrait épargne effectué avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
