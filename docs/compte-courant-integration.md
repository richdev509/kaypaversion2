# Intégration Compte Courant — Document Technique
**KAYPA Version 2 · À l'attention de l'équipe mobile**
_Dernière mise à jour : 22 juin 2026_

---

## 1. Vue d'ensemble

Le module Compte Courant permet aux clients KAYPA d'ouvrir et gérer un compte courant en HTG directement lié à leur profil client existant. Deux nouvelles tables ont été ajoutées à la base de données sans modification des tables existantes.

```
clients  ──────┐
               ├──→  current_accounts  ──────→  current_account_transactions
branches ──────┘
users ─────────┘
```

---

## 2. Tables

### 2.1 `current_accounts`

Stocke un compte courant par client (un client peut avoir au maximum un compte actif à la fois).

| Colonne | Type MySQL | Nullable | Description |
|---|---|---|---|
| `id` | `bigint unsigned` | NON | Clé primaire auto-increment |
| `account_number` | `varchar(255)` | NON | Numéro unique format `KCC-XXXXXXXXXX` (10 chiffres aléatoires) |
| `client_id` | `int` (signé) | NON | FK → `clients.id` |
| `branch_id` | `int` (signé) | OUI | FK → `branches.id` (NULL si sans succursale) |
| `balance` | `decimal(15,2)` | NON | Solde actuel en HTG — défaut `0.00` |
| `status` | `enum` | NON | Valeurs : `actif` · `suspendu` · `cloture` — défaut `actif` |
| `last_fee_charged_at` | `timestamp` | OUI | Date du dernier prélèvement de frais de service mensuel |
| `created_by` | `bigint unsigned` | OUI | FK → `users.id` (agent/admin ayant ouvert le compte) |
| `created_at` | `timestamp` | NON | Date d'ouverture du compte |
| `updated_at` | `timestamp` | NON | Dernière modification |

**Contraintes FK :**
- `client_id` → `ON DELETE RESTRICT` (impossible de supprimer un client avec un compte)
- `branch_id` → `ON DELETE SET NULL`
- `created_by` → `ON DELETE SET NULL`

**Statuts — règles métier :**

| Statut | Description | Dépôt | Retrait |
|---|---|---|---|
| `actif` | Compte opérationnel | ✅ | ✅ |
| `suspendu` | Bloqué temporairement | ❌ | ❌ |
| `cloture` | Fermé définitivement | ❌ | ❌ |

---

### 2.2 `current_account_transactions`

Enregistre chaque mouvement sur un compte courant. Immuable — aucune ligne n'est jamais modifiée ou supprimée.

| Colonne | Type MySQL | Nullable | Description |
|---|---|---|---|
| `id` | `bigint unsigned` | NON | Clé primaire auto-increment |
| `transaction_id` | `varchar(255)` | NON | Identifiant unique ULID (ex: `01HZ...`) — à utiliser côté mobile comme référence |
| `current_account_number` | `varchar(255)` | NON | Dénormalisé — numéro du compte (format `KCC-XXXXXXXXXX`) |
| `current_account_id` | `bigint unsigned` | NON | FK → `current_accounts.id` |
| `client_id` | `int` (signé) | NON | FK → `clients.id` |
| `type` | `enum` | NON | Voir section 3 ci-dessous |
| `amount` | `decimal(15,2)` | NON | Montant de l'opération en HTG (toujours positif) |
| `balance_after` | `decimal(15,2)` | NON | Solde du compte après l'opération |
| `method` | `varchar(255)` | OUI | Mode : `cash` · `moncash` · `bank_transfer` · `system` |
| `reference` | `varchar(255)` | OUI | Référence interne (ex: `DEP-A1B2C3`, `RET-D4E5F6`, `OUVERTURE-KCC-...`, `FRAIS-202606-KCC-...`) |
| `note` | `text` | OUI | Note libre |
| `created_by` | `bigint unsigned` | OUI | FK → `users.id` — NULL pour les opérations système automatiques |
| `created_at` | `timestamp` | NON | Date/heure exacte de l'opération |
| `updated_at` | `timestamp` | NON | — |

**Contraintes FK :**
- `current_account_id` → `ON DELETE RESTRICT`
- `client_id` → `ON DELETE RESTRICT`
- `created_by` → `ON DELETE SET NULL`

---

## 3. Types de transactions

| Valeur `type` | Sens | Impact sur `balance` | Impact caisse physique |
|---|---|---|---|
| `DEPOT` | Dépôt client | `+amount` | `+cash_balance` de la succursale |
| `RETRAIT` | Retrait client | `-amount` | `-cash_balance` de la succursale |
| `FRAIS_OUVERTURE` | Frais d'ouverture (perçus à la création) | aucun (solde reste à 0) | `+cash_balance` de la succursale |
| `FRAIS_SERVICE` | Frais service mensuel (automatique) | `-amount` | aucun (déduction virtuelle) |

> **Important :** `amount` est toujours un nombre positif. Le sens (crédit/débit) est déterminé par le `type`. Pour reconstituer l'historique d'un compte, utiliser `balance_after` qui est stocké sur chaque ligne — pas besoin de recalculer.

---

## 4. Paramètres configurables

Table `app_settings`, groupe `compte_courant` :

| `key` | `value` par défaut | `type` | Description |
|---|---|---|---|
| `cc_frais_ouverture` | `200` | `number` | Frais d'ouverture en GDS |
| `cc_frais_service_mensuel` | `10` | `number` | Frais de service mensuel en HTG |
| `cc_frais_service_actif` | `true` | `boolean` | Active/désactive le prélèvement automatique mensuel |

---

## 5. Règles métier à respecter

### Ouverture de compte
- Un client ne peut avoir qu'**un seul compte courant avec statut `actif`** simultanément
- Le KYC du client doit être vérifié (`clients.status_kyc = 'verified'`) avant ouverture
- Les frais d'ouverture sont enregistrés comme transaction `FRAIS_OUVERTURE` mais ne s'ajoutent pas au solde du compte (le solde démarre à `0.00`)

### Dépôt / Retrait
- Opérations uniquement sur un compte `actif`
- Vérifier que `balance >= amount` avant tout retrait
- Toujours lire le solde en mode `SELECT ... FOR UPDATE` (lock ligne) avant d'écrire pour éviter les conflits de concurrence

### Frais de service mensuel
- Prélevés automatiquement le 1er de chaque mois à 08h00 (tâche planifiée côté backend)
- Seulement si `cc_frais_service_actif = true`
- Seulement si `last_fee_charged_at` est antérieur au 1er du mois courant (ou NULL)
- Après prélèvement, `last_fee_charged_at` est mis à jour avec la date courante

---

## 6. Requêtes de référence

### Compte courant d'un client
```sql
SELECT *
FROM current_accounts
WHERE client_id = :client_id
  AND status = 'actif'
LIMIT 1;
```

### Historique des transactions d'un compte
```sql
SELECT *
FROM current_account_transactions
WHERE current_account_id = :account_id
ORDER BY created_at DESC
LIMIT 20 OFFSET 0;
```

### Vérifier si un frais mensuel est dû
```sql
SELECT id, account_number, balance, last_fee_charged_at
FROM current_accounts
WHERE status = 'actif'
  AND (
      last_fee_charged_at IS NULL
      OR last_fee_charged_at < DATE_FORMAT(NOW(), '%Y-%m-01')
  );
```

### Solde reconstituable depuis les transactions
```sql
-- Le balance_after de la transaction la plus récente = solde actuel
SELECT balance_after
FROM current_account_transactions
WHERE current_account_id = :account_id
ORDER BY created_at DESC
LIMIT 1;
```

---

## 7. Diagramme des tables

```
┌─────────────────────────────────────┐
│           current_accounts          │
├─────────────────────────────────────┤
│ id              bigint unsigned PK  │
│ account_number  varchar UNIQUE      │◄── format KCC-XXXXXXXXXX
│ client_id       int        FK       │──► clients.id
│ branch_id       int        FK NULL  │──► branches.id
│ balance         decimal(15,2)       │
│ status          enum                │    actif | suspendu | cloture
│ last_fee_charged_at  timestamp NULL │
│ created_by      bigint unsigned FK  │──► users.id
│ created_at      timestamp           │
│ updated_at      timestamp           │
└──────────────────┬──────────────────┘
                   │ 1
                   │
                   │ N
┌──────────────────▼──────────────────────────────────────┐
│              current_account_transactions                │
├─────────────────────────────────────────────────────────┤
│ id                     bigint unsigned PK               │
│ transaction_id         varchar UNIQUE                   │◄── ULID
│ current_account_number varchar                          │◄── dénormalisé
│ current_account_id     bigint unsigned FK               │──► current_accounts.id
│ client_id              int FK                           │──► clients.id
│ type                   enum                             │    DEPOT | RETRAIT |
│                                                         │    FRAIS_OUVERTURE |
│                                                         │    FRAIS_SERVICE
│ amount                 decimal(15,2)                    │◄── toujours positif
│ balance_after          decimal(15,2)                    │◄── solde après op
│ method                 varchar NULL                     │    cash | moncash |
│                                                         │    bank_transfer | system
│ reference              varchar NULL                     │
│ note                   text NULL                        │
│ created_by             bigint unsigned FK NULL          │──► users.id
│ created_at             timestamp                        │
│ updated_at             timestamp                        │
└─────────────────────────────────────────────────────────┘
```

---

## 8. Notes d'intégration

- **Monnaie :** tous les montants sont en **HTG** sauf les frais d'ouverture qui sont en **GDS** (configurable dans `app_settings`)
- **`transaction_id` (ULID)** est la référence à utiliser dans l'interface mobile — plus stable que l'`id` auto-increment et triable chronologiquement
- **`balance_after`** est fiable sur chaque transaction — pas besoin de recalculer le solde depuis l'historique
- **Ne jamais modifier ni supprimer** de lignes dans `current_account_transactions` — l'historique est immuable
- **Lecture seule recommandée** côté mobile sur ces deux tables ; toute écriture doit passer par le backend KAYPA V2 pour garantir les verrous et l'atomicité des opérations
