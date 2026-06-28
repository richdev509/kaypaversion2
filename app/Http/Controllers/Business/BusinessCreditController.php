<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessCreditActionLog;
use App\Models\Business\BusinessCreditAlert;
use App\Models\Business\BusinessCreditInterestCharge;
use App\Models\Business\BusinessCreditLimit;
use App\Models\Business\BusinessCreditRatePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusinessCreditController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessCreditLimit::with(['business', 'ratePlan'])
            ->whereIn('status', ['pending', 'active']);

        if ($request->filled('status')) {
            $query = BusinessCreditLimit::with(['business', 'ratePlan'])
                ->where('status', $request->status);
        }

        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('business', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('business_number', 'like', "%{$search}%"));
        }

        $credits = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending' => BusinessCreditLimit::where('status', 'pending')->count(),
            'active'  => BusinessCreditLimit::where('status', 'active')->count(),
        ];

        return view('business.credit.index', compact('credits', 'stats'));
    }

    public function show(BusinessCreditLimit $credit)
    {
        $credit->load(['business', 'ratePlan', 'approvedBy', 'interestCharges', 'alerts.actionLogs.doneBy', 'actionLogs.doneBy']);
        return view('business.credit.show', compact('credit'));
    }

    public function plans()
    {
        $plans = BusinessCreditRatePlan::orderBy('profile')->orderBy('effective_from', 'desc')->get();
        return view('business.credit.plans', compact('plans'));
    }

    public function approve(Request $request, BusinessCreditLimit $credit)
    {
        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        if (! $credit->isPending()) {
            return back()->with('error', 'Ce crédit n\'est pas en attente d\'approbation.');
        }

        // Enforce: only one pending/active credit per business
        $exists = BusinessCreditLimit::where('business_id', $credit->business_id)
            ->where('id', '!=', $credit->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ce business a déjà un crédit pending ou actif.');
        }

        DB::transaction(function () use ($credit, $request) {
            $credit->update([
                'status'      => 'active',
                'starts_at'   => now()->toDateString(),
                'expires_at'  => now()->addMonths($credit->duration_months)->toDateString(),
                'approved_by' => Auth::id(),
            ]);

            BusinessCreditActionLog::create([
                'business_credit_limit_id' => $credit->id,
                'business_id'              => $credit->business_id,
                'action'                   => BusinessCreditActionLog::ACTION_APPROBATION,
                'note'                     => $request->note,
                'done_by'                  => Auth::id(),
            ]);
        });

        return back()->with('success', 'Crédit approuvé.');
    }

    public function chargeInterest(Request $request, BusinessCreditLimit $credit)
    {
        $request->validate([
            'period_start'     => 'required|date',
            'period_end'       => 'required|date|after:period_start',
            'avg_balance_used' => 'required|numeric|min:0',
            'note'             => 'nullable|string|max:500',
        ]);

        if (! $credit->isActive()) {
            return back()->with('error', 'Le crédit doit être actif pour facturer des intérêts.');
        }

        $taux = $credit->getEffectiveTaux();
        $totalDue = round((float) $request->avg_balance_used * ($taux / 100), 2);

        BusinessCreditInterestCharge::create([
            'business_credit_limit_id' => $credit->id,
            'business_id'              => $credit->business_id,
            'period_start'             => $request->period_start,
            'period_end'               => $request->period_end,
            'avg_balance_used'         => $request->avg_balance_used,
            'taux_applied'             => $taux,
            'total_due'                => $totalDue,
            'status'                   => 'pending',
            'note'                     => $request->note,
            'processed_by'             => Auth::id(),
        ]);

        return back()->with('success', "Intérêts de {$totalDue} HTG calculés.");
    }

    public function alerts(Request $request)
    {
        $query = BusinessCreditAlert::with(['business', 'credit'])
            ->where('status', 'open');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $alerts = $query->latest()->paginate(20)->withQueryString();

        return view('business.credit.alerts', compact('alerts'));
    }

    public function showAlert(BusinessCreditAlert $alert)
    {
        $alert->load([
            'business',
            'credit.ratePlan',
            'resolvedBy',
            'actionLogs' => fn ($q) => $q->with('doneBy')->latest(),
        ]);

        return view('business.credit.alert-show', compact('alert'));
    }

    public function logAction(Request $request, BusinessCreditAlert $alert)
    {
        $request->validate([
            'action' => 'required|string|max:50',
            'note'   => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($alert, $request) {
            BusinessCreditActionLog::create([
                'business_credit_limit_id' => $alert->business_credit_limit_id,
                'business_credit_alert_id' => $alert->id,
                'business_id'              => $alert->business_id,
                'action'                   => $request->action,
                'note'                     => $request->note,
                'done_by'                  => Auth::id(),
            ]);

            if ($request->action === BusinessCreditActionLog::ACTION_RESOLUTION) {
                $alert->update([
                    'status'      => 'resolved',
                    'resolved_at' => now(),
                    'resolved_by' => Auth::id(),
                ]);
            } elseif ($request->action === BusinessCreditActionLog::ACTION_ESCALADE) {
                $alert->update(['status' => 'escalated']);
            } elseif (in_array($request->action, [BusinessCreditActionLog::ACTION_APPEL, BusinessCreditActionLog::ACTION_EMAIL, BusinessCreditActionLog::ACTION_VISITE])) {
                $alert->update(['status' => 'contacted']);
            }
        });

        return back()->with('success', 'Action enregistrée.');
    }
}
