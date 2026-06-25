# Intégration Compte Épargne — Document Technique
**KAYPA Version 2 · À l'attention de l'équipe mobile**
_Dernière mise à jour : 24 juin 2026_

---

## 1. Vue d'ensemble

Le module Compte Épargne permet aux clients KAYPA d'ouvrir et gérer un compte épargne en GDS directement lié à leur profil client existant. Deux nouvelles tables ont été ajoutées à la base de données sans modification des tables existantes.

```
clients  ──────┐
               ├──→  savings_accounts  ──────→  savings_account_transactions
branches ──────┘
users ─────────┘
```

> **Différence clé avec le Compte Courant :** le Compte Épargne génère des **intérêts mensuels** crédités automatiquement au client. Il n'y a pas de frais de service mensuel. Un **solde minimum obligatoire** doit être maintenu en permanence.

---

## 2. Tables

### 2.1 `savings_accounts`

Stocke un compte épargne par client (un client peut avoir au maximum un compte actif à la fois).

| Colonne | Type MySQL | Nullable | Description |
|---|---|---|---|
| `id` | `bigint unsigned` | NON | Clé primaire auto-increment |
| `account_number` | `varchar(255)` | NON | Numéro unique format `KCE-XXXXXXXXXX` (10 chiffres aléatoires) |
| `client_id` | `int` (signé) | NON | FK → `clients.id` |
| `branch_id` | `int` (signé) | OUI | FK → `branches.id` (NULL si sans succursale) |
| `balance` | `decimal(15,2)` | NON | Solde actuel en GDS — défaut `0.00` |
| `status` | `enum` | NON | Valeurs : `actif` · `suspendu` · `cloture` — défaut `actif` |
| `last_interest_at` | `timestamp` | OUI | Date du dernier versement d'intérêt mensuel |
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

### 2.2 `savings_account_transactions`

Enregistre chaque mouvement sur un compte épargne. Immuable — aucune ligne n'est jamais modifiée ou supprimée.

| Colonne | Type MySQL | Nullable | Description |
|---|---|---|---|
| `id` | `bigint unsigned` | NON | Clé primaire auto-increment |
| `transaction_id` | `varchar(255)` | NON | Identifiant unique ULID (ex: `01HZ...`) — à utiliser côté mobile comme référence |
| `savings_account_number` | `varchar(255)` | NON | Dénormalisé — numéro du compte (format `KCE-XXXXXXXXXX`) |
| `savings_account_id` | `bigint unsigned` | NON | FK → `savings_accounts.id` |
| `client_id` | `int` (signé) | NON | FK → `clients.id` |
| `type` | `enum` | NON | Voir section 3 ci-dessous |
| `amount` | `decimal(15,2)` | NON | Montant de l'opération en GDS (toujours positif) |
| `balance_after` | `decimal(15,2)` | NON | Solde du compte après l'opération |
| `method` | `varchar(255)` | OUI | Mode : `cash` · `moncash` · `bank_transfer` · `system` |
| `reference` | `varchar(255)` | OUI | Référence interne (ex: `DEP-A1B2C3`, `RET-D4E5F6`, `DEPOT-INIT-KCE-...`, `INT-202606-KCE-...`) |
| `note` | `text` | OUI | Note libre |
| `created_by` | `bigint unsigned` | OUI | FK → `users.id` — NULL pour les opérations système automatiques (intérêts) |
| `created_at` | `timestamp` | NON | Date/heure exacte de l'opération |
| `updated_at` | `timestamp` | NON | — |

**Contraintes FK :**
- `savings_account_id` → `ON DELETE RESTRICT`
- `client_id` → `ON DELETE RESTRICT`
- `created_by` → `ON DELETE SET NULL`

---

## 3. Types de transactions

| Valeur `type` | Sens | Impact sur `balance` | Impact caisse physique | `created_by` |
|---|---|---|---|---|
| `DEPOT` | Dépôt client | `+amount` | `+cash_balance` succursale | Agent / Admin |
| `RETRAIT` | Retrait client | `-amount` | `-cash_balance` succursale | Agent / Admin |
| `FRAIS_OUVERTURE` | Frais d'ouverture à la création (si configuré > 0) | aucun (solde reste au dépôt initial) | `+cash_balance` succursale | Agent / Admin |
| `INTERET` | Intérêt mensuel automatique crédité au client | `+amount` | aucun (crédit virtuel) | NULL (Système) |

> **Important :** `amount` est toujours un nombre positif. Le sens (crédit/débit) est déterminé par le `type`.
> - Types **crédit** (augmentent le solde) : `DEPOT`, `INTERET`
> - Types **débit** (diminuent le solde) : `RETRAIT`
> - `FRAIS_OUVERTURE` : prélevé physiquement mais n'affecte pas le solde initial (le solde démarre au montant du dépôt initial).
>
> Pour reconstituer l'historique d'un compte, utiliser `balance_after` stocké sur chaque ligne — pas besoin de recalculer.

---

## 4. Solde minimum obligatoire

Le compte épargne impose un **solde plancher** configurable (défaut : **500 GDS**) :

1. **Ouverture** — le dépôt initial doit être ≥ solde minimum
2. **Retrait** — `balance - montant_retrait` doit rester ≥ solde minimum
3. Le client ne peut donc jamais vider son compte épargne

Le montant maximum retirable est calculé ainsi :
```
max_retrait = balance - solde_minimum
```

---

## 5. Paramètres configurables

Table `app_settings`, groupe `compte_epargne` :

| `key` | `value` par défaut | `type` | Description |
|---|---|---|---|
| `sce_frais_ouverture` | `0` | `number` | Frais d'ouverture en GDS (0 = gratuit) |
| `sce_taux_interet_mensuel` | `0.5` | `number` | Taux d'intérêt mensuel en % appliqué sur le solde |
| `sce_interet_actif` | `true` | `boolean` | Active/désactive le versement automatique mensuel |
| `sce_solde_minimum` | `500` | `number` | Solde minimum obligatoire en GDS (dépôt initial + plancher retrait) |
| `sce_solde_minimum_interet` | `500` | `number` | Solde minimum pour bénéficier des intérêts |

---

## 6. Règles métier à respecter

### Ouverture de compte
- Un client ne peut avoir qu'**un seul compte épargne avec statut `actif`** simultanément
- Le KYC du client doit être vérifié (`clients.status_kyc = 'verified'`) avant ouverture
- Le dépôt initial doit être ≥ `sce_solde_minimum` (défaut 500 GDS)
- Un dépôt initial `DEPOT` est automatiquement enregistré à l'ouverture ; le solde du compte démarre à ce montant
- Si `sce_frais_ouverture > 0`, une transaction `FRAIS_OUVERTURE` est créée mais n'affecte pas le solde

### Dépôt / Retrait
- Opérations uniquement sur un compte `actif`
- **Un client peut effectuer des opérations dans n'importe quelle succursale**, pas seulement celle où le compte a été ouvert
- Pour un retrait : vérifier que `balance - amount >= sce_solde_minimum` avant d'exécuter
- Toujours lire le solde en mode `SELECT ... FOR UPDATE` (lock ligne) avant d'écrire pour éviter les conflits de concurrence
- L'opérateur (agent/admin) est tracé via `created_by` sur chaque transaction

### Intérêts mensuels
- Calculés et versés automatiquement le 1er de chaque mois à 09h00 (tâche planifiée côté backend)
- Seulement si `sce_interet_actif = true`
- Seulement si le solde ≥ `sce_solde_minimum_interet`
- Seulement si `last_interest_at` est antérieur au 1er du mois courant (ou NULL)
- Formule : `interet = round(balance * (taux / 100), 2)`
- Après versement, `last_interest_at` est mis à jour avec la date courante
- `created_by` est NULL pour ces transactions (opération système)

---

## 7. Requêtes de référence

### Compte épargne d'un client
```sql
SELECT *
FROM savings_accounts
WHERE client_id = :client_id
  AND status = 'actif'
LIMIT 1;
```

### Historique des transactions d'un compte
```sql
SELECT *
FROM savings_account_transactions
WHERE savings_account_id = :account_id
ORDER BY created_at DESC
LIMIT 20 OFFSET 0;
```

### Vérifier le montant maximum retirable
```sql
SELECT
    sa.account_number,
    sa.balance,
    (sa.balance - CAST(ap.value AS DECIMAL(15,2))) AS max_retrait
FROM savings_accounts sa
CROSS JOIN app_settings ap
WHERE sa.id = :account_id
  AND ap.key = 'sce_solde_minimum';
```

### Vérifier si un intérêt mensuel est dû
```sql
SELECT id, account_number, balance, last_interest_at
FROM savings_accounts
WHERE status = 'actif'
  AND (
      last_interest_at IS NULL
      OR last_interest_at < DATE_FORMAT(NOW(), '%Y-%m-01')
  );
```

### Solde reconstituable depuis les transactions
```sql
-- Le balance_after de la transaction la plus récente = solde actuel
SELECT balance_after
FROM savings_account_transactions
WHERE savings_account_id = :account_id
ORDER BY created_at DESC
LIMIT 1;
```

### Transactions par type sur une période
```sql
SELECT
    type,
    COUNT(*)              AS nb_operations,
    SUM(amount)           AS total_montant,
    MAX(created_at)       AS derniere_op
FROM savings_account_transactions
WHERE savings_account_id = :account_id
  AND created_at BETWEEN :date_debut AND :date_fin
GROUP BY type;
```

---

## 8. Diagramme des tables

```
┌──────────────────────────────────────┐
│           savings_accounts           │
├──────────────────────────────────────┤
│ id               bigint unsigned PK  │
│ account_number   varchar UNIQUE      │◄── format KCE-XXXXXXXXXX
│ client_id        int        FK       │──► clients.id
│ branch_id        int        FK NULL  │──► branches.id
│ balance          decimal(15,2)       │◄── en GDS
│ status           enum                │    actif | suspendu | cloture
│ last_interest_at timestamp NULL      │◄── date dernier intérêt versé
│ created_by       bigint unsigned FK  │──► users.id
│ created_at       timestamp           │
│ updated_at       timestamp           │
└──────────────────┬───────────────────┘
                   │ 1
                   │
                   │ N
┌──────────────────▼──────────────────────────────────────────┐
│              savings_account_transactions                    │
├─────────────────────────────────────────────────────────────┤
│ id                      bigint unsigned PK                  │
│ transaction_id          varchar UNIQUE                      │◄── ULID
│ savings_account_number  varchar                             │◄── dénormalisé
│ savings_account_id      bigint unsigned FK                  │──► savings_accounts.id
│ client_id               int FK                              │──► clients.id
│ type                    enum                                │    DEPOT | RETRAIT |
│                                                             │    FRAIS_OUVERTURE |
│                                                             │    INTERET
│ amount                  decimal(15,2)                       │◄── toujours positif, en GDS
│ balance_after           decimal(15,2)                       │◄── solde après op
│ method                  varchar NULL                        │    cash | moncash |
│                                                             │    bank_transfer | system
│ reference               varchar NULL                        │
│ note                    text NULL                           │
│ created_by              bigint unsigned FK NULL             │──► users.id (NULL si système)
│ created_at              timestamp                           │
│ updated_at              timestamp                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 9. Comparaison Compte Courant vs Compte Épargne

| Critère | Compte Courant (`KCC-`) | Compte Épargne (`KCE-`) |
|---|---|---|
| **Monnaie** | HTG | GDS |
| **Frais mensuels** | ✅ Frais service (`FRAIS_SERVICE`) | ❌ Aucun |
| **Intérêts** | ❌ Aucun | ✅ Crédit mensuel automatique (`INTERET`) |
| **Solde minimum** | ❌ Pas de plancher | ✅ 500 GDS (configurable) |
| **Solde initial** | 0.00 (frais d'ouverture ne s'ajoutent pas) | ≥ solde minimum (dépôt initial obligatoire) |
| **Dépôt initial** | Non requis | Obligatoire (≥ 500 GDS) |
| **Table transactions** | `current_account_transactions` | `savings_account_transactions` |
| **Champ suivi intérêt** | `last_fee_charged_at` | `last_interest_at` |

---

## 10. Notes d'intégration

- **Monnaie :** tous les montants sont en **GDS** (Gourdes)
- **`transaction_id` (ULID)** est la référence à utiliser dans l'interface mobile — plus stable que l'`id` auto-increment et triable chronologiquement
- **`balance_after`** est fiable sur chaque transaction — pas besoin de recalculer le solde depuis l'historique
- **`created_by = NULL`** sur une transaction `INTERET` signifie que c'est une opération système automatique, pas une erreur
- **Ne jamais modifier ni supprimer** de lignes dans `savings_account_transactions` — l'historique est immuable
- **Lecture seule recommandée** côté mobile sur ces deux tables ; toute écriture doit passer par le backend KAYPA V2 pour garantir les verrous et l'atomicité des opérations
- **Traçabilité opérateur :** chaque transaction de dépôt ou retrait humain enregistre `created_by` = ID de l'agent ou admin qui a effectué l'opération, quel que soit la succursale où il travaille
