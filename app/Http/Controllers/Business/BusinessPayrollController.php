<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessEmployee;
use App\Models\Business\BusinessEntity;
use App\Models\Business\BusinessPayrollBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessPayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = BusinessPayrollBatch::with('business');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('business', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $batches = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending_approval' => BusinessPayrollBatch::where('status', BusinessPayrollBatch::STATUS_PENDING_APPROVAL)->count(),
            'completed'        => BusinessPayrollBatch::where('status', BusinessPayrollBatch::STATUS_COMPLETED)->count(),
        ];

        return view('business.payroll.index', compact('batches', 'stats'));
    }

    public function show(BusinessPayrollBatch $batch)
    {
        $batch->load(['business', 'approvedBy', 'creator', 'items.employee']);
        return view('business.payroll.show', compact('batch'));
    }

    public function employees(Request $request)
    {
        $query = BusinessEmployee::with('business');

        if ($request->filled('business_id')) {
            $query->where('business_id', $request->business_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('kaypa_account_number', 'like', "%{$search}%");
            });
        }

        $employees = $query->latest()->paginate(25)->withQueryString();
        $businesses = BusinessEntity::orderBy('name')->get(['id', 'name', 'business_number']);

        return view('business.payroll.employees', compact('employees', 'businesses'));
    }

    public function approve(Request $request, BusinessPayrollBatch $batch)
    {
        $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        if (! $batch->isPendingApproval()) {
            return back()->with('error', 'Ce batch n\'est pas en attente d\'approbation.');
        }

        $batch->update([
            'status'      => BusinessPayrollBatch::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'note'        => $request->note ?? $batch->note,
        ]);

        return back()->with('success', "Batch {$batch->reference} approuvé.");
    }
}
