<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Department;
use App\Models\Commune;
use App\Models\City;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index()
    {
        $clients = Client::latest()->paginate(20);
        return view('clients.index', compact('clients'));
    }

    /**
     * Search clients (inclut recherche par numéro de compte)
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        if (empty($search)) {
            return redirect()->route('clients.index');
        }

        $clients = Client::with('accounts')
            ->search($search)
            ->latest()
            ->paginate(20)
            ->appends(['search' => $search]);

        return view('clients.search', compact('clients', 'search'));
    }

    /**
     * Show the form for creating a new client
     */
    public function create()
    {
        // Générer 2 tokens UUID uniques pour les scans
        $uploadToken = Str::uuid()->toString();
        $uploadTokenProfil = Str::uuid()->toString();

        // Stocker les tokens en cache (expiration: 3 minutes)
        $tokenService = new TokenService();
        $tokenService->storeToken($uploadToken);
        $tokenService->storeToken($uploadTokenProfil);

        // Charger les départements pour le menu déroulant
        $departments = Department::orderBy('name')->get();

        return view('clients.create', compact('uploadToken', 'uploadTokenProfil', 'departments'));
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20|unique:clients,phone',
            'email' => 'nullable|email|max:150|unique:clients,email',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F,Autre',
            'nationalite' => 'nullable|string|max:100',
            'piece_type' => 'nullable|in:ID,Permis,Passeport',
            'nu_number' => 'nullable|string|max:10',
            'nui_number' => 'nullable|string|max:10',
            'permis_number' => 'nullable|string|max:13',
            'passport_number' => 'nullable|string|max:9',
            'date_emission' => 'nullable|date',
            'date_expiration' => 'nullable|date',
            'document_id_type' => 'nullable|in:ID,Permis,Passeport',
            'document_id_number' => 'nullable|string|max:255',
            'id_nif_cin' => 'nullable|string|max:255',
            'numero_carte' => 'nullable|string|max:255',
            'status_kyc' => 'nullable|in:pending,verified,rejected,not_verified',
            'kyc' => 'nullable|boolean',
            'piece_id_path' => 'nullable|string|max:500',
            'back_path' => 'nullable|string|max:500',
            'selfie_path' => 'nullable|string|max:500',
            'profil_path' => 'nullable|string|max:500',
            'front_id_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'back_id_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'department_id' => 'nullable|exists:departments,id',
            'commune_id' => 'nullable|exists:communes,id',
            'city_id' => 'nullable|exists:cities,id',
            'code_parrain' => 'nullable|string|max:9',
        ]);

        // Utiliser birth_date si date_naissance est vide
        if (empty($validated['date_naissance']) && !empty($validated['birth_date'])) {
            $validated['date_naissance'] = $validated['birth_date'];
        }
        unset($validated['birth_date']);

        // Utiliser birth_date si date_naissance est vide
        if (empty($validated['date_naissance']) && !empty($validated['birth_date'])) {
            $validated['date_naissance'] = $validated['birth_date'];
        }
        unset($validated['birth_date']);

        // Mapper piece_type vers document_id_type
        if (!empty($validated['piece_type'])) {
            $validated['document_id_type'] = $validated['piece_type'];
        }
        unset($validated['piece_type']);

        // Mapper les numéros de documents selon le type
        if (!empty($validated['nu_number'])) {
            $validated['numero_carte'] = $validated['nu_number'];
            $validated['document_id_number'] = $validated['nu_number'];
        }
        if (!empty($validated['nui_number'])) {
            $validated['id_nif_cin'] = $validated['nui_number'];
        }
        if (!empty($validated['permis_number'])) {
            $validated['document_id_number'] = $validated['permis_number'];
        }
        if (!empty($validated['passport_number'])) {
            $validated['document_id_number'] = $validated['passport_number'];
        }

        // Nettoyer les champs temporaires
        unset($validated['nu_number'], $validated['nui_number'], $validated['permis_number'], $validated['passport_number']);

        // Gérer les photos scannées (chemins reçus depuis le scan mobile)
        if (!empty($validated['piece_id_path'])) {
            $validated['front_id_path'] = $validated['piece_id_path'];
        }
        if (!empty($validated['back_path'])) {
            $validated['back_id_path'] = $validated['back_path'];
        }
        if (!empty($validated['selfie_path']) && is_string($validated['selfie_path'])) {
            // C'est un chemin, pas un fichier
            // On garde tel quel
        }

        // Gérer les uploads de fichiers (si pas de scan)
        if ($request->hasFile('front_id_path')) {
            $validated['front_id_path'] = $request->file('front_id_path')->store('documents/clients/ids', 'public');
        }

        if ($request->hasFile('back_id_path')) {
            $validated['back_id_path'] = $request->file('back_id_path')->store('documents/clients/ids', 'public');
        }

        if ($request->hasFile('selfie_path')) {
            $validated['selfie_path'] = $request->file('selfie_path')->store('documents/clients/selfies', 'public');
        }

        // Nettoyer les champs temporaires
        unset($validated['piece_id_path'], $validated['back_path'], $validated['profil_path']);

        // Générer un client_id unique si non fourni
        if (empty($validated['client_id'])) {
            $validated['client_id'] = 'CL-' . strtoupper(substr(uniqid(), -10));
        }

        // Convertir kyc checkbox en boolean
        $validated['kyc'] = $request->has('kyc') ? 1 : 0;

        // Définir le statut KYC par défaut
        if (empty($validated['status_kyc'])) {
            $validated['status_kyc'] = 'pending';
        }

        // Assigner automatiquement la branche de l'utilisateur connecté
        $validated['branch_id'] = Auth::user()->branch_id;

        // Vérifier et associer le code de parrainage si fourni
        if (!empty($validated['code_parrain'])) {
            $affiliate = \App\Models\Affiliate::where('code_parrain', $validated['code_parrain'])
                ->where('status', 'approuve')
                ->first();

            if ($affiliate) {
                $validated['affiliate_id'] = $affiliate->id;
            } else {
                // Si le code n'existe pas, ne pas l'enregistrer
                unset($validated['code_parrain']);
            }
        }

        $client = Client::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client créé avec succès! ID: ' . $client->client_id);
    }

    /**
     * Display the specified client
     */
    public function show(Client $client)
    {
        $client->load('accounts.plan');
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the client
     */
    public function edit(Client $client)
    {
        $user = Auth::user();

        // Vérifier que l'agent/manager ne modifie que les clients de sa succursale
        if (!in_array($user->role, ['admin', 'comptable'])) {
            if ($user->branch_id != $client->branch_id) {
                abort(403, 'Vous ne pouvez modifier que les clients de votre succursale.');
            }
        }

        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client)
    {
        $user = Auth::user();

        // Vérifier que l'agent/manager ne modifie que les clients de sa succursale
        if (!in_array($user->role, ['admin', 'comptable'])) {
            if ($user->branch_id != $client->branch_id) {
                abort(403, 'Vous ne pouvez modifier que les clients de votre succursale.');
            }
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20|unique:clients,phone,' . $client->id,
            'email' => 'nullable|email|max:150|unique:clients,email,' . $client->id,
            'address' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F,Autre',
            'nationalite' => 'nullable|string|max:100',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Informations du client mises à jour avec succès!');
    }

    /**
     * Remove the specified client
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès!');
    }

    /**
     * AJAX: Obtenir les communes d'un département
     */
    public function getCommunes($departmentId)
    {
        $communes = Commune::where('department_id', $departmentId)
            ->orderBy('name')
            ->get();

        return response()->json($communes);
    }

    /**
     * AJAX: Obtenir les villes d'une commune
     */
    public function getCities($communeId)
    {
        $cities = City::where('commune_id', $communeId)
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }

    /**
     * Afficher l'interface mobile de scan
     */
    public function scanForm($token, Request $request)
    {
        $tokenService = new TokenService();

        // Vérifier que le token est valide
        if ($tokenService->verifyOrCreate($token) == -1) {
            return view('clients.scan-expired');
        }

        $tokenProfil = $request->query('tokenProfil');

        return view('clients.scan', compact('token', 'tokenProfil'));
    }

    /**
     * Recevoir et sauvegarder les photos prises par mobile
     */
    public function scanUpload(Request $request, $token)
    {
        $tokenService = new TokenService();

        // Vérifier que le token est valide
        if ($tokenService->verifyOrCreate($token) == -1) {
            return response()->json([
                'success' => false,
                'message' => 'Le token a expiré'
            ], 400);
        }

        $validated = $request->validate([
            'front' => 'required|string',
            'back' => 'required|string',
            'selfie' => 'required|string',
        ]);

        foreach (['front', 'back', 'selfie'] as $type) {
            // Récupérer les données base64
            $data = $validated[$type];

            // Retirer le préfixe data:image
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);

            // Décoder base64
            $image = base64_decode($data);

            // Générer nom de fichier
            $filename = "client_{$token}_{$type}.jpg";

            // Sauvegarder dans storage/app/public/clients/pieces/
            Storage::disk('public')->put("clients/pieces/{$filename}", $image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photos enregistrées avec succès!'
        ]);
    }

    /**
     * AJAX: Vérifier si les photos ont été uploadées
     */
    public function checkUpload($token)
    {
        // Chemins attendus
        $path_front = "clients/pieces/client_{$token}_front.jpg";
        $path_back = "clients/pieces/client_{$token}_back.jpg";
        $path_selfie = "clients/pieces/client_{$token}_selfie.jpg";

        // Vérifier si la photo AVANT existe
        if (Storage::disk('public')->exists($path_front)) {
            return response()->json([
                'uploaded' => true,

                // Chemins relatifs (pour BDD)
                'path_front' => $path_front,
                'path_back' => $path_back,
                'path_selfie' => $path_selfie,

                // URLs publiques (pour affichage)
                'url_front' => asset('storage/' . $path_front),
                'url_back' => asset('storage/' . $path_back),
                'url_selfie' => asset('storage/' . $path_selfie),
            ]);
        }

        return response()->json(['uploaded' => false]);
    }

    /**
     * AJAX: Vérifier l'unicité du téléphone ou email
     */
    public function checkUnique(Request $request)
    {
        $field = $request->input('field'); // 'phone' ou 'email'
        $value = $request->input('value');
        $clientId = $request->input('client_id'); // Pour édition (ignorer l'enregistrement actuel)

        if (empty($field) || empty($value)) {
            return response()->json(['available' => true]);
        }

        $query = Client::where($field, $value);

        // Si on édite un client existant, l'ignorer
        if ($clientId) {
            $query->where('id', '!=', $clientId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? "Ce {$field} est déjà utilisé par un autre client" : "Ce {$field} est disponible"
        ]);
    }

    /**
     * Vérifier si un code de parrainage existe et est valide
     */
    public function checkCodeParrain(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            return response()->json(['valid' => true, 'message' => '']);
        }

        $affiliate = \App\Models\Affiliate::where('code_parrain', $code)
            ->where('status', 'approuve')
            ->first();

        if ($affiliate) {
            return response()->json([
                'valid' => true,
                'message' => "✓ Code valide - Partenaire: {$affiliate->nom_complet}"
            ]);
        } else {
            return response()->json([
                'valid' => false,
                'message' => '✗ Code de parrainage invalide ou inactif'
            ]);
        }
    }

    /**
     * Vérifier si un document d'identité existe déjà
     */
    public function checkDocument(Request $request)
    {
        $field = $request->input('field'); // 'nu_number' ou 'nui_number'
        $value = $request->input('value');
        $clientId = $request->input('client_id'); // Pour édition (ignorer l'enregistrement actuel)

        if (empty($field) || empty($value)) {
            return response()->json(['available' => true, 'message' => '']);
        }

        // Mapping des champs selon le type
        $dbField = $field;
        if ($field === 'nu_number') {
            $dbField = 'numero_carte'; // ou 'document_id_number'
        } elseif ($field === 'nui_number') {
            $dbField = 'id_nif_cin';
        }

        $query = Client::where($dbField, $value);

        // Si on édite un client existant, l'ignorer
        if ($clientId) {
            $query->where('id', '!=', $clientId);
        }

        $exists = $query->exists();

        $fieldName = $field === 'nu_number' ? 'numéro de carte' : 'NIU';

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? "Ce {$fieldName} est déjà utilisé par un autre client" : "Ce {$fieldName} est disponible"
        ]);
    }

    /**
     * Afficher le formulaire de vérification KYC
     */
    public function verifyKyc(Client $client)
    {
        // Générer token UUID pour le scan
        $uploadToken = Str::uuid()->toString();

        // Stocker le token en cache (expiration: 3 minutes)
        $tokenService = new TokenService();
        $tokenService->storeToken($uploadToken);

        return view('clients.verify-kyc', compact('client', 'uploadToken'));
    }

    /**
     * Mettre à jour les informations KYC du client
     */
    public function updateKyc(Request $request, Client $client)
    {
        // Nettoyer les champs vides selon le type sélectionné
        $pieceType = $request->input('piece_type');

        if ($pieceType !== 'ID') {
            $request->merge(['nu_number' => null, 'nui_number' => null]);
        }
        if ($pieceType !== 'Permis') {
            $request->merge(['permis_number' => null]);
        }
        if ($pieceType !== 'Passeport') {
            $request->merge(['passport_number' => null]);
        }

        $validated = $request->validate([
            'piece_type' => 'nullable|in:ID,Permis,Passeport',
            'nu_number' => [
                'nullable',
                'required_if:piece_type,ID',
                'string',
                'size:10',
                'regex:/^[A-Z0-9]{10}$/i',
                \Illuminate\Validation\Rule::unique('clients', 'numero_carte')->ignore($client->id)
            ],
            'nui_number' => [
                'nullable',
                'required_if:piece_type,ID',
                'string',
                'size:10',
                'regex:/^[0-9]{10}$/',
                \Illuminate\Validation\Rule::unique('clients', 'id_nif_cin')->ignore($client->id)
            ],
            'permis_number' => [
                'nullable',
                'required_if:piece_type,Permis',
                'string',
                'size:13',
                'regex:/^[A-Z]{2}[0-9]{11}$/'
            ],
            'passport_number' => [
                'nullable',
                'required_if:piece_type,Passeport',
                'string',
                'size:9',
                'regex:/^[A-Z]{2}[0-9]{7}$/'
            ],
            'date_emission' => 'nullable|date',
            'date_expiration' => 'nullable|date',
            'status_kyc' => 'nullable|in:pending,verified,rejected,not_verified',
            'kyc' => 'nullable|boolean',
            'piece_id_path' => 'nullable|string|max:500',
            'back_path' => 'nullable|string|max:500',
            'selfie_path' => 'nullable|string|max:500',
            'profil_path' => 'nullable|string|max:500',
        ]);

        // Mapper piece_type vers document_id_type
        if (!empty($validated['piece_type'])) {
            $validated['document_id_type'] = $validated['piece_type'];
        }
        unset($validated['piece_type']);

        // Mapper les numéros de documents selon le type
        if (!empty($validated['nu_number'])) {
            $validated['numero_carte'] = $validated['nu_number'];
            $validated['document_id_number'] = $validated['nu_number'];
        }
        if (!empty($validated['nui_number'])) {
            $validated['id_nif_cin'] = $validated['nui_number'];
        }
        if (!empty($validated['permis_number'])) {
            $validated['document_id_number'] = $validated['permis_number'];
        }
        if (!empty($validated['passport_number'])) {
            $validated['document_id_number'] = $validated['passport_number'];
        }

        // Nettoyer les champs temporaires
        unset($validated['nu_number'], $validated['nui_number'], $validated['permis_number'], $validated['passport_number']);

        // Gérer les photos scannées
        if (!empty($validated['piece_id_path'])) {
            $validated['front_id_path'] = $validated['piece_id_path'];
        }
        if (!empty($validated['back_path'])) {
            $validated['back_id_path'] = $validated['back_path'];
        }
        if (!empty($validated['selfie_path']) && is_string($validated['selfie_path'])) {
            // C'est un chemin, pas un fichier
        }

        // Nettoyer les champs temporaires
        unset($validated['piece_id_path'], $validated['back_path'], $validated['profil_path']);

        // Convertir kyc checkbox en boolean
        $validated['kyc'] = $request->has('kyc') ? 1 : 0;

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Vérification KYC mise à jour avec succès!');
    }

    /**
     * Afficher le formulaire de mise à jour KYC (avec scan QR)
     */
    public function updateKycForm(Client $client)
    {
        // Générer 1 token UUID unique pour le scan
        $uploadToken = Str::uuid()->toString();

        // Stocker le token en cache (expiration: 5 minutes)
        $tokenService = new TokenService();
        $tokenService->storeToken($uploadToken);

        return view('clients.update-kyc', compact('client', 'uploadToken'));
    }

    /**
     * Traiter la mise à jour KYC avec les nouvelles photos
     */
    public function processKycUpdate(Request $request, Client $client)
    {
        // Nettoyer les champs vides selon le type sélectionné
        $docType = $request->input('document_id_type');

        if ($docType !== 'ID') {
            $request->merge(['nu_number' => null, 'nui_number' => null]);
        }
        if ($docType !== 'Permis') {
            $request->merge(['permis_number' => null]);
        }
        if ($docType !== 'Passeport') {
            $request->merge(['passport_number' => null]);
        }

        $validated = $request->validate([
            // Informations d'identité
            'document_id_type' => 'required|in:ID,Permis,Passeport',
            'nu_number' => [
                'required_if:document_id_type,ID',
                'nullable',
                'string',
                'size:10',
                'regex:/^[A-Z0-9]{10}$/i',
                \Illuminate\Validation\Rule::unique('clients', 'numero_carte')->ignore($client->id)
            ],
            'nui_number' => [
                'required_if:document_id_type,ID',
                'nullable',
                'string',
                'size:10',
                'regex:/^[0-9]{10}$/',
                \Illuminate\Validation\Rule::unique('clients', 'id_nif_cin')->ignore($client->id)
            ],
            'permis_number' => [
                'required_if:document_id_type,Permis',
                'nullable',
                'string',
                'size:13',
                'regex:/^[A-Z]{2}[0-9]{11}$/'
            ],
            'passport_number' => [
                'required_if:document_id_type,Passeport',
                'nullable',
                'string',
                'size:9',
                'regex:/^[A-Z]{2}[0-9]{7}$/'
            ],
            'date_emission' => 'required|date|before_or_equal:today',
            'date_expiration' => 'required|date|after:date_emission',

            // Chemins des photos scannées
            'front_path' => 'required|string|max:500',
            'back_path' => 'required|string|max:500',
            'selfie_path' => 'required|string|max:500',
        ]);

        // Mapper le type de document
        $updateData = [
            'document_id_type' => $validated['document_id_type'],
            'date_emission' => $validated['date_emission'],
            'date_expiration' => $validated['date_expiration'],
            'front_id_path' => $validated['front_path'],
            'back_id_path' => $validated['back_path'],
            'selfie_path' => $validated['selfie_path'],
            'status_kyc' => 'pending', // Remettre en pending après mise à jour
        ];

        // Mapper les numéros selon le type de document
        if ($validated['document_id_type'] === 'ID') {
            $updateData['numero_carte'] = $validated['nu_number'];
            $updateData['id_nif_cin'] = $validated['nui_number'];
            $updateData['document_id_number'] = $validated['nu_number'];
        } elseif ($validated['document_id_type'] === 'Permis') {
            $updateData['document_id_number'] = $validated['permis_number'];
        } elseif ($validated['document_id_type'] === 'Passeport') {
            $updateData['document_id_number'] = $validated['passport_number'];
        }

        $client->update($updateData);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Documents KYC mis à jour avec succès! Le statut est passé en attente de vérification.');
    }
}
