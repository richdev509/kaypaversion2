# 📘 DOCUMENTATION TECHNIQUE - SYSTÈME KAYPA
## Système de Gestion des Carnets d'Épargne TIPA

**Version:** 1.0  
**Date:** 27 Novembre 2025  
**Projet Source:** newKaypa  
**Base de données partagée:** mybankkaypa

---

## 🎯 OBJECTIF

Ce document fournit toutes les informations nécessaires pour développer un nouveau projet Laravel qui utilise la **même base de données** que le système KAYPA existant, tout en permettant des améliorations et évolutions indépendantes.

---

## 🔌 CONNEXION À LA BASE DE DONNÉES

### Configuration `.env`

```env
DB_CONNECTION=mysql
DB_HOST=74.208.185.41
DB_PORT=3306
DB_DATABASE=mybankkaypa
DB_USERNAME=richard509
DB_PASSWORD=Dieu098
```

### Configuration `config/database.php`

```php
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '74.208.185.41'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'mybankkaypa'),
    'username' => env('DB_USERNAME', 'richard509'),
    'password' => env('DB_PASSWORD', 'Dieu098'),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

---

## 📊 STRUCTURE DE LA BASE DE DONNÉES

### 1. Table `clients`

**Description:** Informations des clients de KAYPA

```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) UNIQUE,
    email VARCHAR(255) UNIQUE,
    adresse TEXT,
    date_naissance DATE,
    sexe ENUM('M', 'F', 'Autre'),
    numero_piece VARCHAR(50),
    type_piece ENUM('CIN', 'Passeport', 'Permis'),
    photo_profil VARCHAR(255),
    piece_id_path VARCHAR(255),
    front_id_path VARCHAR(255),
    back_id_path VARCHAR(255),
    numero_carte VARCHAR(50),
    status_kyc ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    kaypa_identity_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_telephone (telephone),
    INDEX idx_email (email),
    INDEX idx_status_kyc (status_kyc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Table `plans`

**Description:** Plans d'épargne disponibles

```sql
CREATE TABLE plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    duree INT NOT NULL COMMENT 'Durée en jours',
    montant_ouverture DECIMAL(10, 2) DEFAULT 0.00,
    retrait_autorise TINYINT(1) DEFAULT 1,
    jour_min_retrait INT DEFAULT 0 COMMENT 'Jours minimum avant premier retrait',
    pourcentage_retrait_partiel INT DEFAULT 60 COMMENT 'Pourcentage max pour retrait partiel',
    frais_jour_partiel INT DEFAULT 0 COMMENT 'Pénalité en jours pour retrait partiel',
    frais_jour_total INT DEFAULT 3 COMMENT 'Pénalité en jours pour retrait total anticipé',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Exemples de plans:**
- **TIPA 30 jours** : duree=30, frais_jour_total=3
- **TIPA 60 jours** : duree=60, frais_jour_total=3
- **TIPA 90 jours** : duree=90, frais_jour_total=3

### 3. Table `accounts`

**Description:** Carnets d'épargne individuels

```sql
CREATE TABLE accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id VARCHAR(50) UNIQUE NOT NULL COMMENT 'Identifiant métier (KP-XXXXXXXXXX)',
    client_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    montant_journalier DECIMAL(10, 2) NOT NULL COMMENT 'Montant épargné quotidiennement',
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    status ENUM('actif', 'clos', 'suspendu') DEFAULT 'actif',
    amount_after DECIMAL(12, 2) DEFAULT 0.00 COMMENT 'Solde actuel',
    montant_dispo_retrait DECIMAL(12, 2) DEFAULT 0.00 COMMENT 'Montant disponible pour retrait (max 60%)',
    withdraw DECIMAL(12, 2) DEFAULT 0.00 COMMENT 'Dette de retrait non remboursée',
    retrait_status TINYINT(1) DEFAULT 0 COMMENT '0=pas de dette, 1=dette active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id),
    INDEX idx_account_id (account_id),
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_dates (date_debut, date_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Champs clés:**
- `account_id`: Identifiant unique généré (ex: KP-1234567890)
- `amount_after`: Solde calculé en temps réel (dernière transaction)
- `withdraw`: Dette créée lors d'un retrait partiel
- `montant_dispo_retrait`: Capacité de retrait (bloquée si dette)

### 4. Table `payments`

**Description:** Historique des dépôts

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    method ENUM('cash', 'moncash', 'bank_transfer') NOT NULL,
    reference VARCHAR(100) UNIQUE,
    type VARCHAR(50) DEFAULT 'PAIEMENT',
    note TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_account (account_id),
    INDEX idx_date (payment_date),
    INDEX idx_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5. Table `withdrawals`

**Description:** Historique des retraits

```sql
CREATE TABLE withdrawals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    withdrawal_date DATE NOT NULL,
    method ENUM('cash', 'moncash', 'bank_transfer') NOT NULL,
    note TEXT,
    penalty_applied TINYINT(1) DEFAULT 0 COMMENT 'Pénalité appliquée ou non',
    mode ENUM('partiel', 'total'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_account (account_id),
    INDEX idx_date (withdrawal_date),
    INDEX idx_mode (mode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6. Table `account_transactions`

**Description:** Journal unifié de toutes les transactions (recommandé pour calculs)

```sql
CREATE TABLE account_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_transaction VARCHAR(50) UNIQUE NOT NULL COMMENT 'ULID unique',
    account_id VARCHAR(50) NOT NULL COMMENT 'Référence account_id métier',
    client_id BIGINT UNSIGNED NOT NULL,
    type ENUM('PAIEMENT', 'RETRAIT', 'AJUSTEMENT') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL COMMENT 'Positif=dépôt, Négatif=retrait',
    amount_after DECIMAL(12, 2) NOT NULL COMMENT 'Solde après transaction',
    mode ENUM('partiel', 'total') NULL,
    method ENUM('cash', 'moncash', 'bank_transfer'),
    reference VARCHAR(100),
    note TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_account_id (account_id),
    INDEX idx_client (client_id),
    INDEX idx_type (type),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Utilisation:** Cette table est la **source de vérité** pour le calcul du solde. Le champ `amount_after` contient le solde en temps réel après chaque opération.

### 7. Table `users`

**Description:** Utilisateurs du système (agents, admins)

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent', 'super_admin', 'manager', 'service-client') DEFAULT 'agent',
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🏗️ MODÈLES ELOQUENT

### Model: `Client.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'nom', 'prenom', 'telephone', 'email', 'adresse',
        'date_naissance', 'sexe', 'numero_piece', 'type_piece',
        'photo_profil', 'piece_id_path', 'front_id_path', 'back_id_path',
        'numero_carte', 'status_kyc', 'kaypa_identity_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    // Relations
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Account::class);
    }
}
```

### Model: `Plan.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'duree', 'montant_ouverture',
        'retrait_autorise', 'jour_min_retrait', 'pourcentage_retrait_partiel',
        'frais_jour_partiel', 'frais_jour_total'
    ];

    protected $casts = [
        'retrait_autorise' => 'boolean',
        'duree' => 'integer',
        'jour_min_retrait' => 'integer',
        'pourcentage_retrait_partiel' => 'integer',
        'frais_jour_partiel' => 'integer',
        'frais_jour_total' => 'integer',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
```

### Model: `Account.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    protected $fillable = [
        'account_id', 'client_id', 'plan_id',
        'montant_journalier', 'date_debut', 'date_fin',
        'status', 'amount_after', 'montant_dispo_retrait',
        'withdraw', 'retrait_status'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant_journalier' => 'decimal:2',
        'amount_after' => 'decimal:2',
        'montant_dispo_retrait' => 'decimal:2',
        'withdraw' => 'decimal:2',
        'retrait_status' => 'boolean',
    ];

    protected $appends = ['solde_virtuel'];

    // Attribut calculé: Solde actuel
    public function getSoldeVirtuelAttribute()
    {
        return $this->amount_after ?? 0;
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class, 'account_id', 'account_id');
    }

    // Génération automatique de l'account_id
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->account_id)) {
                $account->account_id = self::generateAccountId();
            }
        });
    }

    public static function generateAccountId()
    {
        do {
            $prefix = 'KP-';
            $random = random_int(1000000000, 9999999999);
            $id = $prefix . $random;

            $exists = self::where('account_id', $id)->exists()
                || DB::table('tbl_code_carnet_manuel')->where('Code', $id)->exists();
        } while ($exists);

        return $id;
    }
}
```

### Model: `Payment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'account_id', 'amount', 'payment_date',
        'method', 'reference', 'type', 'note', 'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
```

### Model: `Withdrawal.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'account_id', 'amount', 'withdrawal_date',
        'method', 'note', 'penalty_applied', 'mode'
    ];

    protected $casts = [
        'withdrawal_date' => 'date',
        'amount' => 'decimal:2',
        'penalty_applied' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
```

### Model: `AccountTransaction.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccountTransaction extends Model
{
    protected $fillable = [
        'id_transaction', 'account_id', 'client_id',
        'type', 'amount', 'amount_after', 'mode',
        'method', 'reference', 'note', 'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_after' => 'decimal:2',
    ];

    // Constantes
    public const TYPE_PAIEMENT = 'PAIEMENT';
    public const TYPE_RETRAIT = 'RETRAIT';
    public const TYPE_AJUSTEMENT = 'AJUSTEMENT';

    public const MODE_PARTIEL = 'partiel';
    public const MODE_TOTAL = 'total';

    // Génération automatique de l'ID transaction
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tx) {
            if (empty($tx->id_transaction)) {
                $tx->id_transaction = (string) Str::ulid();
            }
        });
    }

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAIEMENT);
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_RETRAIT);
    }
}
```

---

## 🔧 SERVICES MÉTIER

### Service: `AccountTransactionService.php`

**Responsabilité:** Calculer le solde incrémental après chaque transaction

```php
<?php

namespace App\Services;

use App\Models\AccountTransaction;

class AccountTransactionService
{
    /**
     * Calcule le nouveau solde après une transaction
     *
     * @param  string  $accountId  Identifiant métier du compte
     * @param  float   $montant    Montant de l'opération
     * @param  string  $type       'deposit' ou 'withdraw'
     * @return float               Nouveau solde
     */
    public function handleTransaction(string $accountId, float $montant, string $type): float
    {
        if (!in_array($type, ['deposit', 'withdraw'])) {
            throw new \InvalidArgumentException("Type invalide : $type");
        }

        // Récupérer la dernière transaction
        $lastTx = AccountTransaction::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        // Solde actuel
        $amountAfter = $lastTx?->amount_after ?? 0;

        // Appliquer l'opération
        if ($type === 'deposit') {
            $amountAfter += $montant;
        } elseif ($type === 'withdraw') {
            $amountAfter -= $montant;
        }

        return $amountAfter;
    }
}
```

### Service: `TransactionService.php`

**Responsabilité:** Gérer la dette de retrait et capacité de retrait

```php
<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Enregistrer un dépôt et gérer la dette
     */
    public function deposit(Account $account, float $amount): Account
    {
        return DB::transaction(function () use ($account, $amount) {
            // Réduire la dette de retrait si elle existe
            if ($account->withdraw > 0) {
                if ($amount <= $account->withdraw) {
                    // Dépôt partiel → réduction de dette
                    $account->withdraw -= $amount;
                    if ($account->withdraw == 0) {
                        $account->retrait_status = 0;
                    }
                } else {
                    // Dépôt supérieur → dette totalement remboursée
                    $account->withdraw = 0;
                    $account->retrait_status = 0;
                }
            }

            // Recalculer la capacité de retrait si dette remboursée
            if ($account->withdraw == 0) {
                if ($account->montant_journalier <= $account->amount_after) {
                    $account->montant_dispo_retrait = $account->amount_after * 0.6;
                }
            }

            // Si montant_dispo_retrait était à 0 avec dette active
            if ($account->montant_dispo_retrait == 0 && $account->withdraw > 0) {
                if ($amount < $account->withdraw) {
                    $account->montant_dispo_retrait += $amount;
                }
            }

            $account->save();
            return $account;
        });
    }

    /**
     * Enregistrer un retrait et créer une dette
     */
    public function withdraw(Account $account, float $amount): array
    {
        return DB::transaction(function () use ($account, $amount) {
            // Vérifier la capacité de retrait
            if ($amount > $account->montant_dispo_retrait) {
                return ['success' => false];
            }

            // Déduire de la capacité et créer une dette
            $account->montant_dispo_retrait -= $amount;
            $account->withdraw += $amount;
            $account->retrait_status = 1;

            $account->save();

            return ['success' => true];
        });
    }
}
```

### Service: `WithdrawalService.php`

**Responsabilité:** Logique complète de retrait (partiel/total avec pénalités)

```php
<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Withdrawal;
use App\Models\AccountTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WithdrawalService
{
    /**
     * Traiter un retrait (partiel ou total)
     *
     * @param Account $account
     * @param float   $amount
     * @param string  $method
     * @param string|null $note
     * @return array ['success' => bool, 'message' => string]
     */
    public static function process(Account $account, float $amount, string $method, ?string $note = null): array
    {
        // Validations
        if ($account->status !== 'actif') {
            return ['success' => false, 'message' => "Retrait impossible : compte {$account->status}"];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => "Montant invalide"];
        }

        // Vérifier multiple du montant journalier
        if ($amount % $account->montant_journalier != 0) {
            return ['success' => false, 'message' => "Le montant doit être un multiple de {$account->montant_journalier} GDS"];
        }

        $solde = $account->solde_virtuel;
        $montantJournalier = $account->montant_journalier;
        $dateFin = Carbon::parse($account->date_fin);
        $dateAujourdhui = Carbon::today();

        if ($amount > $solde) {
            return ['success' => false, 'message' => "Montant supérieur au solde disponible"];
        }

        $penalite = 0;
        $penaliteAppliquee = false;

        // CAS 1: RETRAIT PARTIEL (≤ 60% du solde)
        if ($amount < $solde) {
            if ($amount <= $solde * 0.6) {
                $transactionService = new TransactionService();
                $reponse = $transactionService->withdraw($account, $amount);

                if (!$reponse['success']) {
                    return ['success' => false, 'message' => "Limite de retrait dépassée"];
                }

                Withdrawal::create([
                    'account_id' => $account->id,
                    'amount' => $amount,
                    'withdrawal_date' => $dateAujourdhui->toDateString(),
                    'method' => $method,
                    'note' => $note,
                    'penalty_applied' => false,
                    'mode' => 'partiel'
                ]);
            } else {
                return ['success' => false, 'message' => "Retrait partiel autorisé jusqu'à 60% du solde"];
            }
        }

        // CAS 2: RETRAIT TOTAL (100% du solde)
        if ($amount == $solde) {
            if ($dateAujourdhui->lt($dateFin)) {
                // Plan non terminé → Pénalité de 3 jours
                $penalite = $montantJournalier * 3;
                $net = $amount - $penalite;
                $penaliteAppliquee = true;

                if ($net <= 0) {
                    return ['success' => false, 'message' => "Solde insuffisant après pénalité"];
                }

                Withdrawal::create([
                    'account_id' => $account->id,
                    'amount' => $net,
                    'withdrawal_date' => $dateAujourdhui->toDateString(),
                    'method' => $method,
                    'note' => $note ? $note . " (Pénalité de 3 jours: {$penalite} GDS)" : "Pénalité: {$penalite} GDS",
                    'penalty_applied' => true,
                    'mode' => 'total'
                ]);

                $account->update(['status' => 'clos']);
            } else {
                // Plan terminé → Pas de pénalité
                Withdrawal::create([
                    'account_id' => $account->id,
                    'amount' => $amount,
                    'withdrawal_date' => $dateAujourdhui->toDateString(),
                    'method' => $method,
                    'note' => $note,
                    'penalty_applied' => false,
                    'mode' => 'total'
                ]);

                $account->update(['status' => 'clos']);
            }
        }

        // Enregistrer dans account_transactions
        $service = new AccountTransactionService();
        $amountAfter = $service->handleTransaction($account->account_id, $amount, 'withdraw');
        $account->update(['amount_after' => $amountAfter]);

        AccountTransaction::create([
            'account_id' => $account->account_id,
            'client_id' => $account->client_id,
            'type' => AccountTransaction::TYPE_RETRAIT,
            'amount' => -$amount,
            'amount_after' => $amountAfter,
            'method' => $method,
            'mode' => $amount < $solde ? 'partiel' : 'total',
            'created_by' => Auth::id(),
            'note' => $note
        ]);

        return ['success' => true, 'message' => "Retrait effectué avec succès"];
    }
}
```

---

## 🎮 CONTRÔLEURS

### Controller: `PaymentController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payment;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\AccountTransactionService;
use App\Services\TransactionService;

class PaymentController extends Controller
{
    public function create(Account $account)
    {
        return view('payments.create', compact('account'));
    }

    public function store(Request $request, Account $account)
    {
        $request->validate([
            'method' => 'required|in:cash,moncash,bank_transfer',
            'reference' => 'nullable|string|max:100|unique:payments,reference',
            'amount' => ['required', 'integer', 'numeric', 'min:' . $account->montant_journalier],
        ]);

        if ($account->status !== 'actif') {
            return back()->withErrors(['amount' => "Dépôt impossible : compte {$account->status}"]);
        }

        $montant = $request->amount;
        $montantJournalier = $account->montant_journalier;
        $soldeTotal_Prevue = $account->plan->duree * $montantJournalier;
        $soldeActuel = $account->solde_virtuel;

        // Vérifier multiple exact
        if ($montant % $montantJournalier !== 0) {
            return back()->withErrors(['amount' => "Le montant doit être un multiple de {$montantJournalier} GDS"]);
        }

        // Ne pas dépasser le total prévu
        if (($soldeActuel + $montant) > $soldeTotal_Prevue) {
            $maxPaiement = $soldeTotal_Prevue - $soldeActuel;
            return back()->withErrors(['amount' => "Paiement maximum autorisé: {$maxPaiement} GDS"]);
        }

        $nombreJours = $montant / $montantJournalier;

        DB::transaction(function () use ($request, $account, $montant, $montantJournalier, $nombreJours) {
            // Créer le payment
            Payment::create([
                'account_id' => $account->id,
                'amount' => $montant,
                'type' => 'PAIEMENT',
                'payment_date' => now()->toDateString(),
                'method' => $request->method,
                'reference' => $request->reference,
                'created_by' => Auth::id(),
                'note' => "Paiement de {$nombreJours} jour(s) à {$montantJournalier} GDS/jour"
            ]);

            // Calculer le nouveau solde
            $service = new AccountTransactionService();
            $amountAfter = $service->handleTransaction($account->account_id, $montant, 'deposit');
            $account->update(['amount_after' => $amountAfter]);

            // Gérer la dette de retrait
            $depotService = new TransactionService();
            $depotService->deposit($account, $montant);

            // Créer l'entrée dans account_transactions
            AccountTransaction::create([
                'account_id' => $account->account_id,
                'client_id' => $account->client_id,
                'type' => AccountTransaction::TYPE_PAIEMENT,
                'amount' => $montant,
                'amount_after' => $amountAfter,
                'method' => $request->method,
                'reference' => $request->reference,
                'created_by' => Auth::id(),
                'note' => "Paiement de {$nombreJours} jour(s)"
            ]);
        });

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Dépôt enregistré avec succès');
    }
}
```

### Controller: `WithdrawalController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\WithdrawalService;

class WithdrawalController extends Controller
{
    public function create(Account $account)
    {
        $account->load('client', 'plan');
        return view('withdrawals.create', compact('account'));
    }

    public function store(Request $request, Account $account)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,moncash,bank_transfer',
            'note' => 'nullable|string',
        ]);

        if ($account->status !== 'actif') {
            return back()->with('error', "Retrait impossible : compte {$account->status}");
        }

        $result = WithdrawalService::process(
            $account,
            $request->amount,
            $request->method,
            $request->note
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('accounts.show', $account)
            ->with('success', $result['message']);
    }
}
```

---

## 📐 LOGIQUE MÉTIER DÉTAILLÉE

### 🔵 RÈGLES DE DÉPÔT

1. **Validation du montant:**
   - Montant doit être un **multiple exact** du `montant_journalier`
   - Exemple: Si montant_journalier = 100 GDS, accepter 100, 200, 300... mais pas 150

2. **Limitation par plan:**
   - Le total des dépôts ne peut pas dépasser : `plan.duree × montant_journalier`
   - Exemple: Plan 30 jours à 100 GDS/jour → Maximum 3000 GDS

3. **Remboursement de dette:**
   - Si `account.withdraw > 0`, le dépôt rembourse d'abord cette dette
   - Une fois remboursé, régénérer `montant_dispo_retrait = amount_after × 0.6`

4. **Calcul du nouveau solde:**
   ```
   Nouveau solde = Solde actuel + Montant déposé
   ```

### 🔴 RÈGLES DE RETRAIT

#### **Type 1: Retrait Partiel**

**Conditions:**
- `amount ≤ solde × 0.6` (max 60% du solde)
- `amount ≤ montant_dispo_retrait` (capacité disponible)
- Montant = multiple du `montant_journalier`

**Actions:**
1. Créer un `Withdrawal` avec `mode = 'partiel'`
2. Réduire `montant_dispo_retrait -= amount`
3. Augmenter `withdraw += amount` (dette)
4. Mettre `retrait_status = 1`
5. Compte reste `actif`

**Exemple:**
```
Solde: 10,000 GDS
Retrait: 4,000 GDS (40% ✅)

Après retrait:
- Nouveau solde: 6,000 GDS
- Dette: 4,000 GDS
- Capacité restante: 2,400 GDS (60% de 6,000 - 4,000 dette)
```

#### **Type 2: Retrait Total**

**Conditions:**
- `amount = solde` (100% du solde)

**Si plan non terminé (date < date_fin):**
```
Pénalité = montant_journalier × 3 jours
Montant net = solde - pénalité

Si pénalité >= solde:
   → Refuser (solde insuffisant)
Sinon:
   → Verser montant net au client
   → Clôturer compte (status = 'clos')
```

**Si plan terminé (date >= date_fin):**
```
Pas de pénalité
→ Verser solde complet
→ Clôturer compte (status = 'clos')
```

**Actions:**
1. Créer un `Withdrawal` avec `mode = 'total'`
2. Mettre `account.status = 'clos'`
3. Mettre `amount_after = 0`

---

## 🔄 FLUX TRANSACTIONNEL COMPLET

### Scénario 1: Dépôt Simple

```
État initial:
- Solde: 1,000 GDS
- Dette: 0 GDS
- Capacité retrait: 600 GDS (60%)

Action: Dépôt de 500 GDS

Étapes:
1. Valider: 500 est multiple de montant_journalier ✅
2. AccountTransactionService: 1,000 + 500 = 1,500 GDS
3. Mettre à jour account.amount_after = 1,500
4. TransactionService.deposit(): Pas de dette → Régénérer capacité
   - montant_dispo_retrait = 1,500 × 0.6 = 900 GDS
5. Créer Payment record
6. Créer AccountTransaction record

Résultat:
- Solde: 1,500 GDS ✅
- Dette: 0 GDS
- Capacité retrait: 900 GDS ✅
```

### Scénario 2: Retrait Partiel puis Remboursement

```
État initial:
- Solde: 10,000 GDS
- Dette: 0 GDS
- Capacité retrait: 6,000 GDS

Étape 1: Retrait partiel 4,000 GDS
1. Vérifier: 4,000 ≤ 6,000 (60%) ✅
2. Vérifier: 4,000 ≤ montant_dispo_retrait (6,000) ✅
3. TransactionService.withdraw():
   - montant_dispo_retrait = 6,000 - 4,000 = 2,000
   - withdraw = 0 + 4,000 = 4,000
   - retrait_status = 1
4. AccountTransactionService: 10,000 - 4,000 = 6,000
5. Créer Withdrawal et AccountTransaction

Après retrait:
- Solde: 6,000 GDS
- Dette: 4,000 GDS
- Capacité: 2,000 GDS
- Status: BLOQUÉ (dette active)

Étape 2: Dépôt 5,000 GDS
1. AccountTransactionService: 6,000 + 5,000 = 11,000 GDS
2. TransactionService.deposit():
   - Dette actuelle: 4,000 GDS
   - Dépôt: 5,000 GDS > 4,000
   - Rembourser dette: withdraw = 0
   - retrait_status = 0
   - Régénérer: montant_dispo_retrait = 11,000 × 0.6 = 6,600 GDS
3. Créer Payment et AccountTransaction

Résultat final:
- Solde: 11,000 GDS ✅
- Dette: 0 GDS ✅
- Capacité: 6,600 GDS ✅
- Status: DÉBLOQUÉ
```

### Scénario 3: Retrait Total avec Pénalité

```
État initial:
- Solde: 5,000 GDS
- Montant journalier: 100 GDS
- Date actuelle: 15 jours avant date_fin

Action: Retrait total 5,000 GDS

Étapes:
1. Détecter: amount == solde → Retrait total
2. Vérifier date: aujourd'hui < date_fin → Pénalité applicable
3. Calculer pénalité: 100 × 3 = 300 GDS
4. Montant net: 5,000 - 300 = 4,700 GDS
5. Créer Withdrawal:
   - amount: 4,700 GDS
   - penalty_applied: true
   - mode: 'total'
6. AccountTransactionService: 5,000 - 5,000 = 0
7. Mettre account.status = 'clos'

Résultat:
- Client reçoit: 4,700 GDS
- Pénalité: 300 GDS
- Compte: CLOS
```

---

## 🔐 SÉCURITÉ ET CONTRAINTES

### 1. Contraintes de base de données

```sql
-- Unicité
ALTER TABLE accounts ADD UNIQUE KEY uk_account_id (account_id);
ALTER TABLE payments ADD UNIQUE KEY uk_reference (reference);

-- Index pour performance
CREATE INDEX idx_account_transactions_account ON account_transactions(account_id, created_at DESC);
CREATE INDEX idx_withdrawals_account ON withdrawals(account_id, withdrawal_date);
```

### 2. Validations dans les contrôleurs

```php
// Dépôt
$request->validate([
    'amount' => ['required', 'numeric', 'min:' . $account->montant_journalier],
    'method' => 'required|in:cash,moncash,bank_transfer',
    'reference' => 'nullable|unique:payments,reference'
]);

// Retrait
$request->validate([
    'amount' => ['required', 'numeric', 'min:1'],
    'method' => 'required|in:cash,moncash,bank_transfer'
]);
```

### 3. Permissions utilisateur

```php
// Dans Policy ou Middleware
public function canMakePayment(User $user, Account $account)
{
    return in_array($user->role, ['admin', 'agent']) 
        && $account->status === 'actif';
}

public function canWithdraw(User $user, Account $account)
{
    return in_array($user->role, ['admin', 'agent'])
        && $account->status === 'actif'
        && $account->solde_virtuel > 0;
}
```

---

## 📡 ROUTES API (Optionnel)

### Routes Web

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    // Dépôts
    Route::get('/accounts/{account}/payments/create', [PaymentController::class, 'create'])
        ->name('payments.create');
    Route::post('/accounts/{account}/payments', [PaymentController::class, 'store'])
        ->name('payments.store');

    // Retraits
    Route::get('/accounts/{account}/withdrawals/create', [WithdrawalController::class, 'create'])
        ->name('withdrawals.create');
    Route::post('/accounts/{account}/withdrawals', [WithdrawalController::class, 'store'])
        ->name('withdrawals.store');

    // Transactions
    Route::get('/accounts/{account}/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');
});
```

### Routes API (si besoin mobile)

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Consulter solde
    Route::get('/accounts/{account}/balance', function (Account $account) {
        return response()->json([
            'account_id' => $account->account_id,
            'balance' => $account->solde_virtuel,
            'available_for_withdrawal' => $account->montant_dispo_retrait,
            'has_debt' => $account->retrait_status,
            'debt_amount' => $account->withdraw,
            'status' => $account->status
        ]);
    });

    // Historique transactions
    Route::get('/accounts/{account}/transactions', function (Account $account) {
        $transactions = $account->transactions()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json($transactions);
    });
});
```

---

## 🚀 INSTALLATION DANS NOUVEAU PROJET

### Étape 1: Configuration Laravel

```bash
# Créer nouveau projet Laravel
composer create-project laravel/laravel kaypa-new-system
cd kaypa-new-system

# Configurer .env
cp .env.example .env
php artisan key:generate
```

### Étape 2: Configuration Base de Données

Modifier `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=74.208.185.41
DB_PORT=3306
DB_DATABASE=mybankkaypa
DB_USERNAME=richard509
DB_PASSWORD=Dieu098
```

### Étape 3: Créer les Modèles

```bash
php artisan make:model Client
php artisan make:model Plan
php artisan make:model Account
php artisan make:model Payment
php artisan make:model Withdrawal
php artisan make:model AccountTransaction
```

Copier le contenu des modèles fournis ci-dessus.

### Étape 4: Créer les Services

```bash
php artisan make:service AccountTransactionService
php artisan make:service TransactionService
php artisan make:service WithdrawalService
```

### Étape 5: Créer les Contrôleurs

```bash
php artisan make:controller PaymentController
php artisan make:controller WithdrawalController
```

### Étape 6: Tester la connexion

```php
// Dans routes/web.php
Route::get('/test-db', function () {
    $clients = \App\Models\Client::count();
    $accounts = \App\Models\Account::count();
    
    return "Connexion OK - Clients: {$clients}, Comptes: {$accounts}";
});
```

---

## 📝 POINTS D'ATTENTION

### ⚠️ Partage de Base de Données

**Avantages:**
- ✅ Données en temps réel
- ✅ Pas de synchronisation nécessaire
- ✅ Un seul point de vérité

**Risques:**
- ⚠️ Modifications de schéma affectent les deux projets
- ⚠️ Transactions concurrentes possibles
- ⚠️ Migrations doivent être coordonnées

**Recommandations:**
1. **NE PAS exécuter de migrations** sans coordination
2. **Utiliser des transactions DB** pour les opérations critiques
3. **Ajouter des logs** pour tracer les actions
4. **Tester en environnement staging** avant production

### 🔒 Gestion des Conflits

```php
// Utiliser le verrouillage pessimiste
DB::transaction(function () use ($account, $amount) {
    $account = Account::where('id', $account->id)
        ->lockForUpdate()
        ->first();
    
    // Effectuer les opérations
});
```

### 📊 Monitoring

```php
// Logger les opérations critiques
use Illuminate\Support\Facades\Log;

Log::channel('transactions')->info('Deposit', [
    'account_id' => $account->account_id,
    'amount' => $amount,
    'balance_before' => $balanceBefore,
    'balance_after' => $balanceAfter,
    'user_id' => Auth::id()
]);
```

---

## 🧪 TESTS RECOMMANDÉS

### Test: Dépôt Simple

```php
public function test_deposit_increases_balance()
{
    $account = Account::factory()->create([
        'amount_after' => 1000,
        'montant_journalier' => 100
    ]);

    $response = $this->post(route('payments.store', $account), [
        'amount' => 500,
        'method' => 'cash'
    ]);

    $account->refresh();
    $this->assertEquals(1500, $account->amount_after);
}
```

### Test: Retrait avec Dette

```php
public function test_withdrawal_creates_debt()
{
    $account = Account::factory()->create([
        'amount_after' => 10000,
        'montant_dispo_retrait' => 6000,
        'withdraw' => 0
    ]);

    WithdrawalService::process($account, 4000, 'cash');

    $account->refresh();
    $this->assertEquals(6000, $account->amount_after);
    $this->assertEquals(4000, $account->withdraw);
    $this->assertEquals(1, $account->retrait_status);
}
```

### Test: Remboursement Dette

```php
public function test_deposit_clears_debt()
{
    $account = Account::factory()->create([
        'amount_after' => 6000,
        'withdraw' => 4000,
        'retrait_status' => 1
    ]);

    // Dépôt 5000 GDS
    $service = new AccountTransactionService();
    $newBalance = $service->handleTransaction($account->account_id, 5000, 'deposit');
    $account->update(['amount_after' => $newBalance]);

    $depotService = new TransactionService();
    $depotService->deposit($account, 5000);

    $account->refresh();
    $this->assertEquals(11000, $account->amount_after);
    $this->assertEquals(0, $account->withdraw);
    $this->assertEquals(6600, $account->montant_dispo_retrait);
}
```

---

## 📞 SUPPORT & CONTACT

**Équipe KAYPA:**
- Email: contact@mykaypa.com
- Téléphone: +1 319-201-4309

**Documentation originale:**
- Projet source: `c:\laravelProject\newKaypa\laravel`
- Base de données: `mybankkaypa` sur `74.208.185.41`

---

## 📅 CHANGELOG & ÉVOLUTIONS PRÉVUES

### Version Actuelle: 1.0

**Implémenté:**
- ✅ Système de dépôts avec validation
- ✅ Système de retraits partiels/totaux
- ✅ Gestion de dette automatique
- ✅ Calcul incrémental du solde
- ✅ Pénalités pour retrait anticipé

**Améliorations Futures:**
- 🔜 API REST complète pour mobile
- 🔜 Notifications SMS/Email automatiques
- 🔜 Dashboard analytics temps réel
- 🔜 Export PDF des relevés
- 🔜 Système de rappels automatiques
- 🔜 Intégration avec autres passerelles de paiement

---

## 🎓 FORMATION & ONBOARDING

### Pour les Nouveaux Développeurs

1. **Lire ce document complètement**
2. **Cloner et configurer le projet**
3. **Tester la connexion DB**
4. **Créer un compte test**
5. **Simuler des dépôts/retraits**
6. **Analyser les logs de transactions**

### Ressources Complémentaires

- Laravel Documentation: https://laravel.com/docs
- Eloquent ORM: https://laravel.com/docs/eloquent
- Database Transactions: https://laravel.com/docs/database#database-transactions

---

**FIN DU DOCUMENT**

*Ce document est maintenu par l'équipe technique KAYPA.  
Dernière mise à jour: 27 Novembre 2025*
