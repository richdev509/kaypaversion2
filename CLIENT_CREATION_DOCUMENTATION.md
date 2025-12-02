# 📘 DOCUMENTATION - CRÉATION DE CLIENT KAYPA
## Logique Complète du Formulaire et du Processus

**Version:** 1.0  
**Date:** 27 Novembre 2025  
**Module:** Gestion des Clients

---

## 🎯 VUE D'ENSEMBLE

Le système de création de client KAYPA est un processus multi-étapes qui combine :
- **Formulaire web classique** (données personnelles)
- **Scan mobile via QR Code** (pièce d'identité et selfie)
- **Validation KYC** (Know Your Customer)
- **Création automatique de compte utilisateur** (si email fourni)

---

## 📊 STRUCTURE DE LA TABLE `clients`

### Champs Principaux

```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identité de base
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    middle_name VARCHAR(255),
    
    -- Contact
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    area_code VARCHAR(10),
    
    -- Localisation
    address TEXT,
    department_id BIGINT UNSIGNED,
    commune_id BIGINT UNSIGNED,
    city_id BIGINT UNSIGNED,
    
    -- Informations personnelles
    date_naissance DATE,
    lieu_naissance VARCHAR(100),
    sexe ENUM('M', 'F'),
    nationalite VARCHAR(50),
    
    -- Documents d'identité
    document_id_type ENUM('ID', 'Permis', 'Passeport'),
    document_id_number VARCHAR(50) UNIQUE,
    card_number VARCHAR(50) UNIQUE COMMENT 'Numéro carte (9 chiffres)',
    date_emission DATE,
    date_expiration DATE,
    
    -- Fichiers uploadés
    id_nif_cin_file_path VARCHAR(255) COMMENT 'Photo AVANT de la pièce',
    back_id_path VARCHAR(255) COMMENT 'Photo ARRIÈRE de la pièce',
    selfie_path VARCHAR(255) COMMENT 'Photo selfie du client',
    profil_path VARCHAR(255) COMMENT 'Photo de profil',
    
    -- KYC et vérification
    status_kyc ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    kaypa_identity_id BIGINT UNSIGNED,
    kyc TINYINT(1) DEFAULT 0 COMMENT '1 si documents complets',
    
    -- Organisation
    branch_id BIGINT UNSIGNED,
    client_id VARCHAR(50) UNIQUE COMMENT 'Identifiant métier généré',
    
    -- Authentification (pour app mobile)
    password VARCHAR(255),
    password_reset TINYINT(1) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (commune_id) REFERENCES communes(id),
    FOREIGN KEY (city_id) REFERENCES cities(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_status_kyc (status_kyc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔄 FLUX DU PROCESSUS DE CRÉATION

### Vue d'Ensemble

```
┌─────────────────────────────────────────────────────────────┐
│                  1. AGENT OUVRE LE FORMULAIRE                │
│               Route: GET /clients/create                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│            2. GÉNÉRATION DE TOKENS QR CODE                   │
│   - Token pour scan pièce d'identité (avant/arrière/selfie) │
│   - Token pour photo profil                                  │
│   - Tokens stockés en cache (valides 3 min)                 │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         3. CLIENT SCAN LE QR CODE AVEC SON TÉLÉPHONE        │
│            Route mobile: GET /clients/scan/{token}           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│        4. CLIENT PREND 3 PHOTOS (FRONT / BACK / SELFIE)     │
│            Route: POST /clients/scan/{token}                 │
│   - Photos encodées en base64                                │
│   - Sauvegardées dans storage/clients/pieces/               │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│     5. VÉRIFICATION AUTOMATIQUE CÔTÉ PC (AJAX POLLING)      │
│      Route: GET /clients/check-upload/{token}                │
│   - Vérification toutes les 5 secondes                      │
│   - Affichage prévisualisation si upload réussi             │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         6. AGENT REMPLIT LES AUTRES INFORMATIONS            │
│   - Nom, prénom, téléphone, email                           │
│   - Date de naissance, lieu, sexe                           │
│   - Adresse (département, commune, ville)                   │
│   - Type de pièce et numéro                                 │
│   - Dates d'émission et expiration                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              7. SOUMISSION DU FORMULAIRE                     │
│               Route: POST /clients/store                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  8. VALIDATIONS SERVEUR                      │
│   - Vérification unicité téléphone/email                    │
│   - Vérification format des numéros de documents            │
│   - Vérification cohérence dates                            │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│               9. CRÉATION CLIENT EN BDD                      │
│   - Génération client_id automatique                        │
│   - Calcul du statut KYC (kyc = 1 si docs complets)        │
│   - Affectation à la branche de l'agent                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│    10. CRÉATION COMPTE UTILISATEUR (SI EMAIL FOURNI)        │
│   - Génération mot de passe aléatoire                       │
│   - Envoi email de bienvenue                                │
│   - Rôle: 'client'                                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│            11. REDIRECTION VERS LISTE CLIENTS                │
│                  Message: "Client enregistré"                │
└─────────────────────────────────────────────────────────────┘
```

---

## 📝 DÉTAILS DU CONTRÔLEUR

### Méthode: `create()`

**Route:** `GET /clients/create`

**Responsabilité:** Afficher le formulaire de création

```php
public function create()
{
    // 1. Générer 2 tokens UUID uniques
    $uploadToken = Str::uuid()->toString();      // Pour scan pièce
    $uploadTokenProfil = Str::uuid()->toString(); // Pour photo profil
    
    // 2. Stocker les tokens en cache (expiration: 3 minutes)
    $TokenServices = new TokenService();
    $TokenServices->storeToken($uploadToken);
    $TokenServices->storeToken($uploadTokenProfil);
    
    // 3. Charger les départements pour le menu déroulant
    $departments = Department::orderBy('name')->get();
    
    // 4. Retourner la vue avec les données
    return view('clients.create', compact(
        'uploadToken',
        'uploadTokenProfil',
        'departments'
    ));
}
```

**Données passées à la vue:**
- `$uploadToken` : Token pour générer QR Code scan pièce
- `$uploadTokenProfil` : Token pour générer QR Code photo profil
- `$departments` : Liste des départements d'Haïti

---

### Méthode: `store(Request $request)`

**Route:** `POST /clients/store`

**Responsabilité:** Valider et enregistrer le nouveau client

#### Étape 1: Validation des Données

```php
$request->validate([
    // Identité
    'first_name' => 'required|min:2',
    'last_name' => 'required|min:2',
    
    // Contact (unicité vérifiée)
    'phone' => 'required|unique:clients',
    'email' => 'nullable|email|unique:clients',
    
    // Photo pièce (chemin stocké)
    'piece_id_path' => 'nullable|string|max:255',
    
    // Localisation (relations)
    'department_id' => 'required|exists:departments,id',
    'commune_id' => 'required|exists:communes,id',
    'city_id' => 'required|exists:cities,id',
    
    // Informations personnelles
    'date_naissance' => 'nullable|date',
    'lieu_naissance' => 'nullable|string|max:100',
    'sexe' => 'nullable|in:Masculin,Féminin',
    'address' => 'nullable|string|max:255',
    
    // Documents d'identité
    'date_emission' => 'nullable|date',
    'date_expiration' => 'nullable|date|after_or_equal:date_emission',
    'piece_type' => 'nullable|string|max:50',
    
    // Numéros selon type de document (unicité importante)
    'nui_number' => 'nullable|string|max:10|unique:clients,document_id_number',
    'nu_number' => 'nullable|string|max:9|unique:clients,numero_carte',
    'permis_number' => 'nullable|string|max:50|unique:clients,document_id_number',
    'passport_number' => 'nullable|string|max:50|unique:clients,document_id_number',
    
    // Photos
    'profil_path' => 'nullable|string|max:255',
]);
```

#### Étape 2: Vérifications Supplémentaires

```php
// Double vérification téléphone (sécurité)
if (Client::where('phone', $request->phone)->exists()) {
    return redirect()->back()
        ->withErrors(['phone' => 'Le téléphone est déjà utilisé']);
}

// Double vérification email
if (isset($request->email) && Client::where('email', $request->email)->exists()) {
    return redirect()->back()
        ->withErrors(['email' => 'L\'email est déjà utilisé']);
}
```

#### Étape 3: Calcul du Statut KYC

```php
$kyc = 0;

// KYC = 1 si tous les documents obligatoires sont fournis
if ($request->piece_type && 
    $request->piece_type != '' && 
    $request->piece_id_path && 
    $request->piece_id_path != '' && 
    $request->date_emission && 
    $request->date_emission != '' && 
    $request->date_expiration && 
    $request->date_expiration != '') {
    
    $kyc = 1; // Documents complets
}
```

#### Étape 4: Extraction du Numéro de Document

Selon le type de pièce, extraire le bon numéro :

```php
$document_id_number = match ($request->piece_type) {
    'ID' => $request->nui_number,        // NIU (10 chiffres)
    'Permis' => $request->permis_number,  // Format: 123-456-789-0
    'Passeport' => $request->passport_number, // 9 caractères
    default => null,
};
```

#### Étape 5: Création du Client

```php
Client::create([
    // Identité
    'first_name' => $request->first_name,
    'last_name' => $request->last_name,
    
    // Contact
    'phone' => $request->phone,
    'email' => $request->email,
    
    // Organisation
    'branch_id' => Auth::user()->branch_id, // Branche de l'agent
    
    // Localisation
    'department_id' => $request->department_id,
    'commune_id' => $request->commune_id,
    'city_id' => $request->city_id,
    'address' => $request->address,
    
    // Informations personnelles
    'date_naissance' => $request->birth_date,
    'lieu_naissance' => $request->lieu_naissance,
    'sexe' => $request->sexe === 'Masculin' ? 'M' : 'F',
    
    // Documents
    'document_id_type' => $request->piece_type,
    'document_id_number' => $document_id_number,
    'card_number' => $request->nu_number,
    'date_emission' => $request->date_emission,
    'date_expiration' => $request->date_expiration,
    
    // Photos uploadées
    'id_nif_cin_file_path' => $request->piece_id_path, // AVANT
    'back_id_path' => $request->back_path,              // ARRIÈRE
    'selfie_path' => $request->selfie_path,             // SELFIE
    'profil_path' => $request->profil_path,             // PROFIL
    
    // Statut KYC
    'kyc' => $kyc,
]);
```

#### Étape 6: Création Compte Utilisateur (Optionnel)

Si un email est fourni, créer automatiquement un compte utilisateur :

```php
if ($request->email && $request->email != '') {
    // Générer mot de passe aléatoire de 8 caractères
    $password = Str::random(8);
    
    // Créer l'utilisateur
    $user = User::create([
        'name' => $request->first_name . ' ' . $request->last_name,
        'email' => $request->email,
        'telephone' => $request->phone,
        'password' => Hash::make($password),
        'branch_id' => $request->branch_id,
        'role' => 'client',
    ]);
    
    // Envoyer email de bienvenue avec mot de passe
    Mail::to($user->email)->send(new WelcomeUserMail($user, $password));
}
```

#### Étape 7: Redirection

```php
return redirect()->route('clients.index')
    ->with('success', 'Client enregistré.');
```

---

## 📱 SYSTÈME DE SCAN MOBILE

### Méthode: `scanForm($token, Request $request)`

**Route:** `GET /clients/scan/{token}?tokenProfil={tokenProfil}`

**Responsabilité:** Afficher l'interface mobile de scan

```php
public function scanForm($token, Request $request)
{
    // Token principal (pièce d'identité)
    $mainToken = $token;
    
    // Token secondaire (photo profil) via query string
    $tokenProfil = $request->query('tokenProfil');
    
    return view('clients.scan', compact('token', 'tokenProfil'));
}
```

**Interface Mobile:**
- Page optimisée pour smartphone
- 3 boutons de capture photo :
  1. Photo AVANT de la pièce
  2. Photo ARRIÈRE de la pièce
  3. Selfie du client

---

### Méthode: `scanUpload(Request $request, $token)`

**Route:** `POST /clients/scan/{token}`

**Responsabilité:** Recevoir et sauvegarder les photos prises par mobile

#### Validation

```php
$validated = $request->validate([
    'photo_front' => 'required|string',  // Base64
    'photo_back' => 'required|string',   // Base64
    'photo_selfie' => 'required|string', // Base64
]);
```

#### Vérification Token

```php
$TokenServices = new TokenService();

// Vérifier que le token est valide (< 3 minutes)
if ($TokenServices->verifyOrCreate($token) == -1) {
    return redirect()->route('clients.scan', $token)
        ->with('error', 'Le token a expiré. Veuillez réessayer.');
}
```

#### Sauvegarde des Photos

```php
foreach (['front', 'back', 'selfie'] as $type) {
    // 1. Récupérer les données base64
    $data = $validated["photo_{$type}"];
    
    // 2. Retirer le préfixe data:image
    $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
    
    // 3. Décoder base64
    $image = base64_decode($data);
    
    // 4. Générer nom de fichier
    $filename = "client_{$token}_{$type}.jpg";
    
    // 5. Sauvegarder dans storage/app/public/clients/pieces/
    Storage::disk('public')->put("clients/pieces/{$filename}", $image);
}
```

**Chemins générés:**
- `storage/app/public/clients/pieces/client_{token}_front.jpg`
- `storage/app/public/clients/pieces/client_{token}_back.jpg`
- `storage/app/public/clients/pieces/client_{token}_selfie.jpg`

---

### Méthode: `checkUpload($token)`

**Route:** `GET /clients/check-upload/{token}`

**Responsabilité:** Vérifier si les photos ont été uploadées (AJAX polling)

```php
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
```

**Utilisation côté JavaScript:**
```javascript
// Vérifier toutes les 5 secondes
setInterval(checkUpload, 5000);

function checkUpload() {
    fetch(`/clients/check-upload/${token}`)
        .then(res => res.json())
        .then(data => {
            if (data.uploaded) {
                // Remplir les champs cachés
                document.getElementById('piece_id_path').value = data.path_front;
                document.getElementById('back_path').value = data.path_back;
                document.getElementById('selfie_path').value = data.path_selfie;
                
                // Afficher prévisualisations
                document.getElementById('preview').src = data.url_front;
                document.getElementById('preview').classList.remove('hidden');
                
                // Masquer le QR Code
                document.getElementById('piece-id-section').classList.add('hidden');
            }
        });
}
```

---

## 🔐 GESTION DES TOKENS

### Service: `TokenService`

**Responsabilité:** Gérer l'expiration des tokens de scan

```php
class TokenService
{
    /**
     * Stocker un token en cache (expiration: 3 minutes)
     */
    public function storeToken(string $token): void
    {
        Cache::put("scan_token_{$token}", true, now()->addMinutes(3));
    }
    
    /**
     * Vérifier si un token est valide
     * 
     * @return int  1 si valide, -1 si expiré
     */
    public function verifyOrCreate(string $token): int
    {
        if (Cache::has("scan_token_{$token}")) {
            return 1; // Token valide
        }
        
        return -1; // Token expiré
    }
}
```

**Pourquoi 3 minutes?**
- Temps suffisant pour scanner le QR et prendre les photos
- Assez court pour éviter la réutilisation malveillante
- Empêche les tokens de rester en cache indéfiniment

---

## 🌍 GESTION DE LA LOCALISATION (AJAX)

### Hiérarchie Géographique

```
Département (10)
    ↓
Commune (~144)
    ↓
Ville (~nombreuses)
```

### Méthode: `getCommunes($departmentId)`

**Route:** `GET /get-communes/{departmentId}`

```php
public function getCommunes($departmentId)
{
    $communes = Commune::where('department_id', $departmentId)
        ->orderBy('name')
        ->get();
        
    return response()->json($communes);
}
```

**Utilisation JavaScript:**
```javascript
document.getElementById('department').addEventListener('change', function() {
    let departmentId = this.value;
    let communeSelect = document.getElementById('commune');
    
    communeSelect.innerHTML = '<option value="">-- Chargement... --</option>';
    communeSelect.disabled = true;
    
    if (departmentId) {
        fetch(`/get-communes/${departmentId}`)
            .then(res => res.json())
            .then(data => {
                communeSelect.innerHTML = '<option value="">-- Sélectionner --</option>';
                data.forEach(commune => {
                    communeSelect.innerHTML += 
                        `<option value="${commune.id}">${commune.name}</option>`;
                });
                communeSelect.disabled = false;
            });
    }
});
```

### Méthode: `getCities($communeId)`

**Route:** `GET /get-cities/{communeId}`

```php
public function getCities($communeId)
{
    $cities = City::where('commune_id', $communeId)
        ->orderBy('name')
        ->get();
        
    return response()->json($cities);
}
```

---

## 📋 TYPES DE DOCUMENTS D'IDENTITÉ

### 1. Carte d'Identité Nationale (ID)

**Champs requis:**
- `card_number` : 9 chiffres (ex: 123456789)
- `nui_number` : 10 chiffres (NIU - Numéro d'Identification Unique)

**Format:**
```
Carte: 001-234-567-8 (avec tirets visuels)
Stocké: 001234567 (9 chiffres sans tirets)

NIU: 0123456789 (10 chiffres)
```

**Validation JavaScript:**
```javascript
// Limiter à 9 chiffres pour carte
<input name="nu_number" type="text" maxlength="9" pattern="\d{9}">

// Limiter à 10 chiffres pour NIU
<input name="nui_number" type="text" pattern="\d{10}" maxlength="10">
```

### 2. Permis de Conduire (Permis)

**Champs requis:**
- `permis_number` : Format `123-456-789-0` (13 caractères avec tirets)

**Validation et Formatage Auto:**
```javascript
const permisInput = document.getElementById("permis_number");

permisInput.addEventListener("input", (e) => {
    // Retirer tous les non-chiffres
    let value = e.target.value.replace(/\D/g, "").slice(0, 10);
    
    // Formatter avec tirets
    let formatted = value
        .replace(/(\d{3})(\d{3})(\d{3})(\d{1})/, "$1-$2-$3-$4")
        .replace(/-$/, "");
        
    e.target.value = formatted;
});
```

**Exemple:**
- Utilisateur tape: `1234567890`
- Affiché automatiquement: `123-456-789-0`

### 3. Passeport (Passeport)

**Champs requis:**
- `passport_number` : 9 caractères alphanumériques (ex: `AA1234567`)

**Format:**
```
AA1234567
│ └────┬────┘
│      └─ 7 chiffres
└─ 2 lettres
```

---

## 🎨 INTERFACE UTILISATEUR (BLADE)

### Structure du Formulaire

```html
<form action="{{ route('clients.store') }}" method="POST">
    @csrf
    
    <!-- SECTION 1: INFORMATIONS PERSONNELLES -->
    <div class="grid grid-cols-2 gap-4">
        <input name="first_name" placeholder="Prénom" required>
        <input name="last_name" placeholder="Nom" required>
        <input name="phone" placeholder="Téléphone" required>
        <input name="email" placeholder="Email (optionnel)">
        <input name="birth_date" type="date" required>
        <input name="lieu_naissance" placeholder="Lieu de naissance">
        <select name="sexe">
            <option value="Masculin">Masculin</option>
            <option value="Féminin">Féminin</option>
        </select>
    </div>
    
    <!-- SECTION 2: LOCALISATION (CASCADE) -->
    <select name="department_id" id="department">
        <!-- Options chargées depuis BDD -->
    </select>
    <select name="commune_id" id="commune" disabled>
        <!-- Chargé dynamiquement via AJAX -->
    </select>
    <select name="city_id" id="city" disabled>
        <!-- Chargé dynamiquement via AJAX -->
    </select>
    <input name="address" placeholder="Adresse complète">
    
    <!-- SECTION 3: TYPE DE PIÈCE -->
    <select name="piece_type" id="piece_type">
        <option value="">-- Sélectionner --</option>
        <option value="ID">Carte d'identité</option>
        <option value="Permis">Permis de conduire</option>
        <option value="Passeport">Passeport</option>
    </select>
    
    <!-- CHAMPS CONDITIONNELS (affichés selon le type) -->
    <div id="nui_field" class="hidden">
        <input name="nu_number" maxlength="9" placeholder="Numéro carte">
        <input name="nui_number" maxlength="10" placeholder="NIU">
    </div>
    
    <div id="permis_field" class="hidden">
        <input name="permis_number" maxlength="13" placeholder="123-456-789-0">
    </div>
    
    <div id="passport_field" class="hidden">
        <input name="passport_number" maxlength="9" placeholder="AA1234567">
    </div>
    
    <!-- SECTION 4: DATES DE VALIDITÉ -->
    <input name="date_emission" type="date" required>
    <input name="date_expiration" type="date" required>
    
    <!-- SECTION 5: SCAN QR CODE -->
    <div id="piece-id-section">
        <input type="hidden" id="upload_token" value="{{ $uploadToken }}">
        
        <!-- QR Code généré -->
        {!! QrCode::size(120)->generate(route('clients.scan', [
            'token' => $uploadToken,
            'tokenProfil' => $uploadTokenProfil
        ])) !!}
        
        <p>Scannez ce QR avec un téléphone pour prendre les photos</p>
    </div>
    
    <!-- Champs cachés pour stocker les chemins -->
    <input type="hidden" name="piece_id_path" id="piece_id_path">
    <input type="hidden" name="back_path" id="back_path">
    <input type="hidden" name="selfie_path" id="selfie_path">
    <input type="hidden" name="profil_path" id="profil_path">
    
    <!-- Prévisualisation -->
    <img id="preview" src="" class="hidden w-40 border rounded">
    
    <!-- BOUTONS -->
    <button type="submit">Enregistrer</button>
    <a href="{{ route('clients.index') }}">Annuler</a>
</form>
```

### JavaScript Dynamique

#### 1. Affichage Conditionnel des Champs

```javascript
document.getElementById("piece_type").addEventListener("change", () => {
    const type = document.getElementById("piece_type").value;
    
    // Masquer tous les champs
    document.getElementById("nui_field").classList.add("hidden");
    document.getElementById("permis_field").classList.add("hidden");
    document.getElementById("passport_field").classList.add("hidden");
    
    // Afficher le champ correspondant
    if (type === "ID") {
        document.getElementById("nui_field").classList.remove("hidden");
    } else if (type === "Permis") {
        document.getElementById("permis_field").classList.remove("hidden");
    } else if (type === "Passeport") {
        document.getElementById("passport_field").classList.remove("hidden");
    }
});
```

#### 2. Polling Upload (vérification automatique)

```javascript
const token = document.getElementById('upload_token').value;

function checkUpload() {
    fetch(`/clients/check-upload/${token}`)
        .then(res => res.json())
        .then(data => {
            if (data.uploaded) {
                // ✅ Photos uploadées avec succès
                
                // Remplir les champs cachés
                document.getElementById('piece_id_path').value = data.path_front;
                document.getElementById('back_path').value = data.path_back;
                document.getElementById('selfie_path').value = data.path_selfie;
                document.getElementById('profil_path').value = data.path_selfie;
                
                // Afficher prévisualisation
                document.getElementById('preview').src = data.url_front;
                document.getElementById('preview').classList.remove('hidden');
                
                // Masquer le QR Code
                document.getElementById('piece-id-section').classList.add('hidden');
                
                // Afficher message de succès
                document.getElementById('upload-status').classList.remove('hidden');
            }
        });
}

// Vérifier toutes les 5 secondes
setInterval(checkUpload, 5000);
```

---

## ✅ RÈGLES DE VALIDATION

### Validation Serveur (Laravel)

```php
[
    // Champs obligatoires
    'first_name' => 'required|min:2',
    'last_name' => 'required|min:2',
    'phone' => 'required|unique:clients',
    'department_id' => 'required|exists:departments,id',
    'commune_id' => 'required|exists:communes,id',
    'city_id' => 'required|exists:cities,id',
    'birth_date' => 'nullable|date',
    
    // Unicité importante
    'email' => 'nullable|email|unique:clients',
    'nui_number' => 'nullable|string|max:10|unique:clients,document_id_number',
    'nu_number' => 'nullable|string|max:9|unique:clients,numero_carte',
    'permis_number' => 'nullable|string|max:50|unique:clients,document_id_number',
    'passport_number' => 'nullable|string|max:50|unique:clients,document_id_number',
    
    // Cohérence des dates
    'date_emission' => 'nullable|date',
    'date_expiration' => 'nullable|date|after_or_equal:date_emission',
    
    // Formats spécifiques
    'sexe' => 'nullable|in:Masculin,Féminin',
    'piece_type' => 'nullable|in:ID,Permis,Passeport',
]
```

### Validation Client (HTML5)

```html
<!-- Format téléphone -->
<input name="phone" type="tel" pattern="[0-9+\-\s]+" required>

<!-- Email -->
<input name="email" type="email">

<!-- Date de naissance (majeur uniquement) -->
<input name="birth_date" type="date" max="{{ date('Y-m-d', strtotime('-18 years')) }}">

<!-- NIU (10 chiffres uniquement) -->
<input name="nui_number" pattern="\d{10}" maxlength="10" title="10 chiffres">

<!-- Carte (9 chiffres) -->
<input name="nu_number" pattern="\d{9}" maxlength="9" title="9 chiffres">
```

---

## 🚨 GESTION DES ERREURS

### Erreurs Courantes

#### 1. Téléphone Déjà Utilisé

```php
if (Client::where('phone', $request->phone)->exists()) {
    return redirect()->back()
        ->withErrors(['phone' => 'Le téléphone est déjà utilisé par un autre client.'])
        ->withInput();
}
```

**Affichage:**
```blade
@if ($errors->has('phone'))
    <span class="text-red-500">{{ $errors->first('phone') }}</span>
@endif
```

#### 2. Token Expiré (Scan)

```php
if ($TokenServices->verifyOrCreate($token) == -1) {
    return redirect()->route('clients.scan', $token)
        ->with('error', 'Le token a expiré. Veuillez réessayer.');
}
```

**Solution:** Retourner au formulaire et régénérer un nouveau QR Code

#### 3. Date d'Expiration Incohérente

```php
'date_expiration' => 'nullable|date|after_or_equal:date_emission'
```

Si `date_expiration < date_emission` → Erreur automatique

---

## 🔄 STATUT KYC (Know Your Customer)

### Calcul Automatique

```php
$kyc = 0;

// Documents COMPLETS requis pour kyc = 1
if ($request->piece_type &&           // Type renseigné
    $request->piece_id_path &&         // Photo AVANT uploadée
    $request->date_emission &&         // Date émission
    $request->date_expiration) {       // Date expiration
    
    $kyc = 1; // ✅ KYC complet
}
```

### États Possibles

| Valeur | État | Description |
|--------|------|-------------|
| `0` | Incomplet | Documents manquants |
| `1` | Complet | Tous documents fournis |

**Note:** Le champ `status_kyc` (pending/verified/rejected) est distinct et géré par un administrateur lors de la vérification manuelle.

---

## 📊 MODÈLE ELOQUENT

### Relations

```php
class Client extends Model
{
    // Localisation
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
    
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    
    // Organisation
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    // Carnets d'épargne
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
    
    // Paiements (via carnets)
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Account::class);
    }
    
    // Compte utilisateur
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
```

---

## 📁 STOCKAGE DES FICHIERS

### Structure des Dossiers

```
storage/
└── app/
    └── public/
        └── clients/
            └── pieces/
                ├── client_{token}_front.jpg   (Photo AVANT pièce)
                ├── client_{token}_back.jpg    (Photo ARRIÈRE pièce)
                └── client_{token}_selfie.jpg  (Selfie client)
```

### Accès Public

Pour rendre les fichiers accessibles via URL :

```bash
php artisan storage:link
```

Crée un lien symbolique :
```
public/storage -> storage/app/public
```

**URLs générées:**
```
https://votredomaine.com/storage/clients/pieces/client_{token}_front.jpg
```

---

## 🔐 SÉCURITÉ

### 1. Validation des Tokens

- Tokens stockés en cache avec expiration (3 min)
- Vérification à chaque upload
- Token unique par session

### 2. Unicité des Données

```php
// Téléphone, email, numéros de documents : UNIQUE en BDD
'phone' => 'unique:clients',
'email' => 'unique:clients',
'document_id_number' => 'unique:clients',
```

### 3. Upload Sécurisé

```php
// Seulement images base64
// Pas d'exécution de fichiers
// Stockage dans storage/ (non exécutable)
Storage::disk('public')->put("clients/pieces/{$filename}", $image);
```

### 4. Affectation Automatique à la Branche

```php
'branch_id' => Auth::user()->branch_id
```

→ Empêche un agent de créer un client pour une autre branche

---

## 🎯 POINTS CLÉS À RETENIR

### ✅ BONNES PRATIQUES

1. **Toujours valider côté serveur** même si validation HTML5
2. **Vérifier l'unicité** des téléphones/emails/documents
3. **Tokens avec expiration courte** (3 min) pour la sécurité
4. **Photos en base64** pour compatibilité mobile
5. **AJAX polling** pour vérifier uploads sans rafraîchir la page
6. **Cascade géographique** Département → Commune → Ville
7. **KYC automatique** mais vérification manuelle recommandée

### ⚠️ PIÈGES À ÉVITER

1. **Ne pas oublier** de vider les caches de tokens expirés
2. **Ne pas accepter** les uploads sans validation de token
3. **Ne pas stocker** les mots de passe en clair (Hash::make)
4. **Ne pas permettre** la création sans branche assignée
5. **Ne pas oublier** de valider les dates (émission < expiration)

---

## 🚀 AMÉLIORATIONS FUTURES

### 1. Reconnaissance Faciale

```php
// Comparer selfie avec photo de la pièce
$faceService = new FaceRecognitionService();
$match = $faceService->compare($selfie, $photo_piece);

if ($match < 0.8) {
    return back()->with('error', 'Les photos ne correspondent pas');
}
```

### 2. OCR (Extraction Automatique)

```php
// Extraire automatiquement le NIU de la photo
$ocrService = new OCRService();
$extracted = $ocrService->extract($photo_piece);

$request->merge(['nui_number' => $extracted['niu']]);
```

### 3. Notifications SMS

```php
// Envoyer SMS de confirmation
SMS::to($client->phone)->send(
    "Bienvenue chez KAYPA ! Votre compte a été créé avec succès."
);
```

### 4. Dashboard Client Mobile

```php
// API pour application mobile client
Route::middleware('auth:client-api')->group(function () {
    Route::get('/me', [ClientController::class, 'profile']);
    Route::get('/accounts', [ClientController::class, 'myAccounts']);
});
```

---

## 📞 SUPPORT & CONTACT

**Équipe KAYPA:**
- Email: contact@mykaypa.com
- Téléphone: +1 319-201-4309

**Documentation technique:**
- Projet source: `c:\laravelProject\newKaypa\laravel`
- Base de données: `mybankkaypa` sur `74.208.185.41`

---

**FIN DU DOCUMENT**

*Ce document est maintenu par l'équipe technique KAYPA.  
Dernière mise à jour: 27 Novembre 2025*
