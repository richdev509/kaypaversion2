<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessEntity;
use App\Models\Client;
use App\Services\Business\BusinessEntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BusinessEntityController extends Controller
{
    public function __construct(private BusinessEntityService $service) {}

    public function index(Request $request)
    {
        $query = BusinessEntity::with(['ownerClient', 'currentAccount', 'savingsAccount']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('legal_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_kyc')) {
            $query->where('status_kyc', $request->status_kyc);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $entities = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'          => BusinessEntity::count(),
            'kyc_verified'   => BusinessEntity::where('status_kyc', 'verified')->count(),
            'kyc_pending'    => BusinessEntity::where('status_kyc', 'pending')->count(),
            'active'         => BusinessEntity::where('status', 'active')->count(),
        ];

        return view('business.entities.index', compact('entities', 'stats'));
    }

    public function create()
    {
        return view('business.entities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_client_id' => 'required|integer|exists:clients,id',
            'name'            => 'required|string|max:255',
            'legal_name'      => 'nullable|string|max:255',
            'profile'         => 'required|in:standard,etabli,premium',
            'address'         => 'nullable|string|max:500',
            'city'            => 'nullable|string|max:100',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255|sometimes',
            'rccm'            => 'nullable|string|max:100',
            'nif'             => 'nullable|string|max:50',
        ]);

        // Colonnes NOT NULL sans valeur par défaut — éviter d'envoyer NULL
        if (empty($validated['legal_name'])) {
            $validated['legal_name'] = $validated['name'];
        }
        $validated['phone'] = $validated['phone'] ?? '';
        $validated['email'] = $validated['email'] ?? '';

        // Vérification KYC côté serveur — le contrôle JS seul n'est pas suffisant
        $owner = Client::find($validated['owner_client_id']);
        if ($owner->status_kyc !== 'verified') {
            throw ValidationException::withMessages([
                'owner_client_id' => 'Ce client doit avoir un KYC vérifié avant de pouvoir créer un business.',
            ]);
        }

        try {
            $entity = $this->service->create($validated, Auth::id());
            return redirect()
                ->route('business.entities.show', $entity)
                ->with('success', "Business {$entity->business_number} créé avec succès.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(BusinessEntity $entity)
    {
        $entity->load([
            'ownerClient',
            'currentAccount.transactions' => fn ($q) => $q->latest()->limit(10),
            'savingsAccount',
            'businessUsers.client',
            'creditLimits' => fn ($q) => $q->whereIn('status', ['pending', 'active'])->with('ratePlan'),
        ]);

        return view('business.entities.show', compact('entity'));
    }

    public function edit(BusinessEntity $entity)
    {
        return view('business.entities.edit', compact('entity'));
    }

    public function update(Request $request, BusinessEntity $entity)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'profile'    => 'required|in:standard,etabli,premium',
            'address'    => 'nullable|string|max:500',
            'city'       => 'nullable|string|max:100',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'rccm'       => 'nullable|string|max:100',
            'nif'        => 'nullable|string|max:50',
        ]);

        if (empty($validated['legal_name'])) {
            $validated['legal_name'] = $validated['name'];
        }
        $validated['phone'] = $validated['phone'] ?? '';
        $validated['email'] = $validated['email'] ?? '';

        $entity->update($validated);

        return redirect()
            ->route('business.entities.show', $entity)
            ->with('success', 'Business mis à jour avec succès.');
    }

    public function verifyKyc(Request $request, BusinessEntity $entity)
    {
        $request->validate([
            'document_ref' => 'nullable|string|max:255',
        ]);

        if ($entity->isKycVerified()) {
            return back()->with('error', 'Le KYC est déjà vérifié pour ce business.');
        }

        try {
            $this->service->verifyKyc($entity, Auth::id());
            return back()->with('success', 'KYC vérifié avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, BusinessEntity $entity)
    {
        $request->validate([
            'status' => 'required|in:active,suspended',
        ]);

        try {
            $this->service->updateStatus($entity, $request->status);
            return back()->with('success', 'Statut mis à jour.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function openCurrentAccount(BusinessEntity $entity)
    {
        try {
            $account = $this->service->openCurrentAccount($entity, Auth::id());
            return redirect()
                ->route('business.kcb.show', $account)
                ->with('success', "Compte courant {$account->account_number} ouvert.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function openSavingsAccount(BusinessEntity $entity)
    {
        try {
            $account = $this->service->openSavingsAccount($entity, Auth::id());
            return redirect()
                ->route('business.keb.show', $account)
                ->with('success', "Compte épargne {$account->account_number} ouvert.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
