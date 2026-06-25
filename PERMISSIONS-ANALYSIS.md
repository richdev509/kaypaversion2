# 📋 ANALYSE COMPLÈTE DES PERMISSIONS KAYPA

## Permissions Utilisées Dans L'Application

### 1. Dashboard
- `dashboard.view` - Voir le tableau de bord

### 2. Branches (Succursales)
- `branches.view` - Voir les branches
- `branches.create` - Créer des branches
- `branches.edit` - Modifier les branches
- `branches.delete` - Supprimer les branches

### 3. Users (Utilisateurs)
- `users.view` - Voir la liste des utilisateurs
- `users.create` - Créer des utilisateurs
- `users.edit` - Modifier les utilisateurs
- `users.delete` - Supprimer les utilisateurs

### 4. Clients
- `clients.view` - Voir les clients
- `clients.create` - Créer des clients
- `clients.edit` - Modifier les clients
- `clients.delete` - Supprimer les clients
- `verify_kyc` - Vérifier KYC clients

### 5. Accounts (Comptes)
- `accounts.view` - Voir les comptes
- `accounts.create` - Créer des comptes
- `accounts.edit` - Modifier les comptes
- `accounts.delete` - Supprimer les comptes
- `manage_account_status` - Gérer le statut des comptes

### 6. Transactions
- `transactions.view` - Voir les transactions
- `transactions.create` - Créer des transactions
- `transactions.edit` - Modifier les transactions
- `transactions.delete` - Supprimer des transactions
- `create_deposits` - Créer des dépôts
- `create_withdrawals` - Créer des retraits
- `cancel_transactions` - Annuler des transactions
- `create_adjustments` - Créer des ajustements

### 7. Fund Movements (Mouvements de Fonds)
- `fund-movements.view` - Voir les mouvements de fonds
- `fund-movements.create` - Créer des mouvements de fonds
- `fund-movements.edit` - Modifier les mouvements de fonds
- `fund-movements.delete` - Supprimer les mouvements de fonds
- `fund-movements.approve` - Approuver les mouvements de fonds
- `fund-movements.reject` - Rejeter les mouvements de fonds

### 8. Branch Cash (Caisse Succursale)
- `branch-cash.view` - Voir la caisse
- `branch-cash.manage` - Gérer la caisse

### 9. Reports (Rapports)
- `reports.view` - Voir les rapports
- `reports.create` - Créer des rapports
- `reports.generate` - Générer des rapports
- `reports.edit` - Modifier les rapports
- `reports.delete` - Supprimer les rapports

### 10. Plans
- `plans.view` - Voir les plans d'épargne
- `plans.create` - Créer des plans
- `plans.edit` - Modifier les plans
- `plans.delete` - Supprimer les plans
- `manage_plans` - Gérer les plans

### 11. Roles & Permissions (Administration)
- `roles.view` - Voir les rôles
- `roles.create` - Créer des rôles
- `roles.edit` - Modifier les rôles
- `roles.delete` - Supprimer des rôles
- `permissions.view` - Voir les permissions
- `permissions.create` - Créer des permissions
- `permissions.edit` - Modifier des permissions
- `permissions.delete` - Supprimer des permissions
- `manage_roles` - Gérer les rôles
- `manage_permissions` - Gérer les permissions

---

## Configuration des Rôles

### 👑 ADMIN (Accès Total)
**Toutes les permissions ci-dessus**

### 👨‍💼 MANAGER (Gestionnaire)
- dashboard.view
- branches.view, branches.create, branches.edit
- clients.view, clients.create, clients.edit, verify_kyc
- accounts.view, accounts.create, accounts.edit
- transactions.view, create_deposits, create_withdrawals
- reports.view, reports.generate
- fund-movements.view, fund-movements.create, fund-movements.approve
- branch-cash.view, branch-cash.manage
- users.view, users.create, users.edit

### 💼 COMPTABLE
- dashboard.view
- clients.view
- accounts.view
- transactions.view, create_deposits, create_withdrawals, cancel_transactions, create_adjustments
- reports.view, reports.generate
- fund-movements.view
- branch-cash.view

### 👤 AGENT
- dashboard.view
- clients.view, clients.create, clients.edit
- accounts.view, accounts.create
- transactions.view, create_deposits, create_withdrawals

### 🎧 SUPPORT
- dashboard.view
- clients.view
- accounts.view
- transactions.view

---

## Total des Permissions

**Environ 60-70 permissions** couvrant tous les modules de l'application.

L'admin doit avoir **TOUTES** ces permissions pour un contrôle total du système.
