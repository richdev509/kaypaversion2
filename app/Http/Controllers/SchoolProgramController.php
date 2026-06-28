<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SchoolProgram;
use App\Models\SchoolProgramEnrollment;
use App\Services\SchoolProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolProgramController extends Controller
{
    public function __construct(private SchoolProgramService $service) {}

    public function index(Request $request)
    {
        $query = SchoolProgram::withCount('enrollments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $programs = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'         => SchoolProgram::count(),
            'actifs'        => SchoolProgram::where('status', 'actif')->count(),
            'inscrits'      => SchoolProgramEnrollment::count(),
            'coupons_actifs'=> SchoolProgramEnrollment::where('coupon_status', 'active')->count(),
        ];

        return view('school-program.index', compact('programs', 'stats'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        return view('school-program.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string|max:2000',
            'date_debut'             => 'required|date',
            'date_fin'               => 'required|date|after_or_equal:date_debut',
            'inscription_debut'      => 'required|date',
            'inscription_fin'        => 'required|date|after_or_equal:inscription_debut',
            'solde_minimum_epargne'  => 'required|numeric|min:1',
            'montant_blocage'        => 'required|numeric|min:1',
            'duree_blocage_jours'    => 'required|integer|min:1|max:365',
            'tier1_seuil'            => 'required|numeric|min:1',
            'tier1_coupon'           => 'required|numeric|min:1',
            'tier2_seuil'            => 'required|numeric|min:1|gt:tier1_seuil',
            'tier2_coupon'           => 'required|numeric|min:1|gt:tier1_coupon',
        ]);

        $validated['created_by'] = Auth::id();

        $program = SchoolProgram::create($validated);

        return redirect()
            ->route('school-programs.show', $program)
            ->with('success', "Programme \"{$program->name}\" créé avec succès.");
    }

    public function show(SchoolProgram $program)
    {
        $program->load('creator');

        $eligibleClients = $this->service->getEligibleClients($program);

        $enrollments = SchoolProgramEnrollment::where('school_program_id', $program->id)
            ->with(['client', 'savingsAccount', 'usedByAffiliate', 'enrolledBy'])
            ->latest()
            ->paginate(25);

        return view('school-program.show', compact('program', 'eligibleClients', 'enrollments'));
    }

    public function edit(SchoolProgram $program)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        return view('school-program.edit', compact('program'));
    }

    public function update(Request $request, SchoolProgram $program)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string|max:2000',
            'date_debut'             => 'required|date',
            'date_fin'               => 'required|date|after_or_equal:date_debut',
            'inscription_debut'      => 'required|date',
            'inscription_fin'        => 'required|date|after_or_equal:inscription_debut',
            'solde_minimum_epargne'  => 'required|numeric|min:1',
            'montant_blocage'        => 'required|numeric|min:1',
            'duree_blocage_jours'    => 'required|integer|min:1|max:365',
            'tier1_seuil'            => 'required|numeric|min:1',
            'tier1_coupon'           => 'required|numeric|min:1',
            'tier2_seuil'            => 'required|numeric|min:1|gt:tier1_seuil',
            'tier2_coupon'           => 'required|numeric|min:1|gt:tier1_coupon',
        ]);

        $program->update($validated);

        return redirect()
            ->route('school-programs.show', $program)
            ->with('success', 'Programme mis à jour.');
    }

    public function destroy(SchoolProgram $program)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $program->update(['status' => 'archive']);

        return redirect()
            ->route('school-programs.index')
            ->with('success', "Programme \"{$program->name}\" archivé.");
    }

    public function enroll(SchoolProgram $program, Client $client)
    {
        try {
            $enrollment = $this->service->enroll($client, $program, Auth::user());
            return back()->with('success', "Client inscrit. Coupon : {$enrollment->coupon_code}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function bulkEnroll(SchoolProgram $program)
    {
        $result = $this->service->bulkEnroll($program, Auth::user());

        $message = "{$result['enrolled']} client(s) inscrit(s)";
        if ($result['skipped'] > 0) {
            $message .= ", {$result['skipped']} ignoré(s)";
        }

        return redirect()
            ->route('school-programs.show', $program)
            ->with('success', $message)
            ->with('bulk_errors', $result['errors']);
    }
}
