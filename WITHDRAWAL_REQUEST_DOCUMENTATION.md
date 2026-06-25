# 📋 Documentation - Table `withdrawal_requests`

## 🗂️ Structure de la Table

| Colonne | Type | Nullable | Description |
|---------|------|----------|-------------|
| `id` | bigint | Non | Identifiant unique auto-incrémenté |
| `reference_id` | varchar(50) | Non | Numéro de référence unique (WD_XXXXX) |
| `account_id` | varchar(100) | Non | ID du compte épargne du client |
| `client_id` | varchar(100) | Non | Code SAGA du client (ex: SAGA-001) |
| `amount` | decimal(15,2) | Non | Montant demandé en GDS |
| `method` | enum | Non | Méthode de retrait: 'wallet' ou 'bank' |
| `wallet_phone` | varchar(8) | Oui | Numéro de téléphone (8 chiffres) pour wallet |
| `bank_name` | varchar(100) | Oui | Nom de la banque pour virement |
| `bank_account_number` | varchar(50) | Oui | Numéro de compte bancaire |
| `bank_account_holder` | varchar(100) | Oui | Nom du titulaire du compte bancaire |
| `status` | enum | Non | Statut: pending/processing/completed/rejected/cancelled |
| `admin_note` | text | Oui | Note de l'administrateur (raison rejet, etc.) |
| `processed_by` | varchar(255) | Oui | Nom/ID de l'admin qui a traité |
| `processed_at` | timestamp | Oui | Date et heure du traitement |
| `transaction_id` | varchar(255) | Oui | ID de la transaction si complété |
| `balance_before` | decimal(15,2) | Oui | Solde du compte avant le retrait |
| `balance_after` | decimal(15,2) | Oui | Solde du compte après le retrait |
| `created_at` | timestamp | Oui | Date de création de la demande |
| `updated_at` | timestamp | Oui | Date de dernière modification |
| `deleted_at` | timestamp | Oui | Date de suppression (soft delete) |

## 🔍 Index et Contraintes

### Index Composés
- `client_id` + `status` : Recherche rapide des demandes d'un client par statut
- `account_id` + `status` : Recherche rapide des demandes d'un compte par statut
- `created_at` : Tri chronologique des demandes

### Index Unique
- `reference_id` : Garantit l'unicité du numéro de référence

## 📊 Valeurs Enum

### `method` (Méthode de Retrait)
- **wallet** : Retrait via portefeuille mobile (MonCash, NatCash, etc.)
- **bank** : Retrait par virement bancaire

### `status` (Statut de la Demande)
- **pending** : En attente de traitement (état initial)
- **processing** : En cours de traitement par l'admin
- **completed** : Traité et argent envoyé au client
- **rejected** : Demande rejetée (solde insuffisant, infos erronées, etc.)
- **cancelled** : Annulé par le client ou l'admin

## 🔄 Cycle de Vie d'une Demande

```
1. CLIENT FAIT LA DEMANDE
   ↓
   Status: pending
   - reference_id généré (WD_12345)
   - amount, method, wallet_phone ou bank_* enregistrés
   - balance_before et balance_after calculés
   - created_at = maintenant

2. ADMIN VOIT LA DEMANDE
   ↓
   Status: pending → processing
   - processed_by = nom de l'admin
   - processed_at = maintenant

3a. ADMIN APPROUVE                    3b. ADMIN REJETTE
    ↓                                     ↓
    Status: processing → completed        Status: processing → rejected
    - Déduction du montant                - admin_note = raison du rejet
    - transaction_id créé                 - Aucune déduction
    - Transfert effectué (wallet/bank)    - Client notifié
    - Client notifié

4. ANNULATION (optionnel)
   ↓
   Status: pending/processing → cancelled
   - Possible uniquement avant completed
   - admin_note = raison de l'annulation
```

## 💡 Règles Métier

### Montants
- **Minimum** : 50 GDS
- **Maximum** : Limité par le solde disponible (amount_after)

### Méthode Wallet
- **Obligatoire** : `wallet_phone` (exactement 8 chiffres)
- **Facultatif** : bank_name, bank_account_number, bank_account_holder (NULL)
- **Format** : Numérique uniquement (12345678)

### Méthode Bank
- **Obligatoires** : `bank_name`, `bank_account_number`, `bank_account_holder`
- **Facultatif** : wallet_phone (NULL)
- **Exemples** : 
  - bank_name: "Sogebank", "BUH", "Capital Bank"
  - bank_account_number: "1234567890"
  - bank_account_holder: "Jean Dupont"

### Vérifications Avant Création
1. ✅ Client authentifié
2. ✅ Compte existe et appartient au client
3. ✅ Montant ≥ 50 GDS
4. ✅ Solde suffisant (amount ≤ amount_after)
5. ✅ Si wallet → phone exactement 8 chiffres
6. ✅ Si bank → tous les 3 champs remplis

### Déduction du Solde
⚠️ **Important** : Le solde n'est PAS déduit lors de la création de la demande.

La déduction se fait **uniquement** quand :
- L'admin change le status à **completed**
- Une transaction de type **"retrait"** est créée dans `account_transactions`
- Le `amount_after` du compte est mis à jour

**Avantages** :
- Évite les fonds bloqués si rejet
- Permet vérification manuelle avant transfert
- Historique complet avec balance_before et balance_after

## 📱 Exemples de Données

### Exemple 1 : Retrait Wallet (En attente)
```
id: 1
reference_id: WD_00001
account_id: ACC_12345
client_id: SAGA-001
amount: 500.00
method: wallet
wallet_phone: 12345678
bank_name: NULL
bank_account_number: NULL
bank_account_holder: NULL
status: pending
admin_note: NULL
processed_by: NULL
processed_at: NULL
transaction_id: NULL
balance_before: 5000.00
balance_after: 4500.00
created_at: 2025-12-20 10:30:00
updated_at: 2025-12-20 10:30:00
deleted_at: NULL
```

### Exemple 2 : Retrait Bank (Complété)
```
id: 2
reference_id: WD_00002
account_id: ACC_12345
client_id: SAGA-001
amount: 2000.00
method: bank
wallet_phone: NULL
bank_name: Sogebank
bank_account_number: 9876543210
bank_account_holder: Marie Legrand
status: completed
admin_note: Virement effectué avec succès
processed_by: admin@kaypa.com
processed_at: 2025-12-20 15:45:00
transaction_id: TXN_RETRAIT_00002
balance_before: 8000.00
balance_after: 6000.00
created_at: 2025-12-20 14:00:00
updated_at: 2025-12-20 15:45:00
deleted_at: NULL
```

### Exemple 3 : Retrait Rejeté
```
id: 3
reference_id: WD_00003
account_id: ACC_12345
client_id: SAGA-001
amount: 10000.00
method: wallet
wallet_phone: 87654321
bank_name: NULL
bank_account_number: NULL
bank_account_holder: NULL
status: rejected
admin_note: Solde insuffisant au moment du traitement
processed_by: admin@kaypa.com
processed_at: 2025-12-20 16:20:00
transaction_id: NULL
balance_before: 5000.00
balance_after: -5000.00
created_at: 2025-12-20 16:00:00
updated_at: 2025-12-20 16:20:00
deleted_at: NULL
```

## 🔐 Sécurité

### Contrôles d'Accès
- Seul le **propriétaire du compte** peut créer une demande
- Seuls les **administrateurs** peuvent traiter (approuver/rejeter)
- Les clients voient **uniquement leurs propres demandes**

### Validation des Données
- Montant : nombre positif ≥ 50
- Wallet phone : regex `/^\d{8}$/`
- Status transitions contrôlées (pas de pending → completed direct)
- Reference_id unique généré automatiquement

### Soft Delete
- `deleted_at` permet de garder l'historique
- Les demandes supprimées sont exclues des requêtes normales
- Récupération possible si nécessaire

## 📈 Requêtes Utiles

### Voir toutes les demandes en attente
```sql
SELECT * FROM withdrawal_requests 
WHERE status = 'pending' 
AND deleted_at IS NULL
ORDER BY created_at ASC;
```

### Historique d'un client
```sql
SELECT * FROM withdrawal_requests 
WHERE client_id = 'SAGA-001' 
AND deleted_at IS NULL
ORDER BY created_at DESC;
```

### Demandes traitées aujourd'hui
```sql
SELECT * FROM withdrawal_requests 
WHERE DATE(processed_at) = CURDATE()
AND status IN ('completed', 'rejected')
ORDER BY processed_at DESC;
```

### Total des retraits complétés ce mois
```sql
SELECT SUM(amount) as total_retraits
FROM withdrawal_requests 
WHERE status = 'completed'
AND MONTH(processed_at) = MONTH(CURDATE())
AND YEAR(processed_at) = YEAR(CURDATE());
```

## 🚀 Prochaines Fonctionnalités

### À Développer
- [ ] Panel admin pour traiter les demandes
- [ ] Notifications email/SMS au client (status change)
- [ ] Historique des modifications (audit log)
- [ ] Limite de retrait quotidien/mensuel
- [ ] Frais de retrait selon la méthode
- [ ] Export PDF des demandes
- [ ] Statistiques et rapports

---

**Date de création** : 20 Décembre 2025  
**Version** : 1.0  
**Système** : KAYPA - Système de Demande de Retrait
