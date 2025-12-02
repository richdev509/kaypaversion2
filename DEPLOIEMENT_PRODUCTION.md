# GUIDE DE DÉPLOIEMENT PRODUCTION - KAYPA v2

## ⚠️ AVANT DE COMMENCER

**IMPORTANT:** Ce guide est pour déployer les nouvelles fonctionnalités sur une base de données de production existante SANS perdre les données.

---

## 📋 PRÉ-REQUIS

1. Accès SSH au serveur de production
2. Accès à la base de données MySQL
3. Backup récent de la base de données
4. Code source à jour sur le serveur

---

## 🚀 ÉTAPES DE DÉPLOIEMENT

### 1. BACKUP DE LA BASE DE DONNÉES (OBLIGATOIRE)

```bash
# Créer un backup complet
mysqldump -u root -p mybankkaypa > backup_kaypa_$(date +%Y%m%d_%H%M%S).sql

# Vérifier le backup
ls -lh backup_kaypa_*.sql
```

### 2. MIGRATION GESTION FINANCIÈRE

```bash
# Exécuter UNIQUEMENT la migration fund_movements
php artisan migrate --path=database/migrations/production

# Vérifier que la table existe
php artisan tinker --execute="echo Schema::hasTable('fund_movements') ? '✅ OK' : '❌ Erreur';"
```

### 3. INSTALLER SPATIE PERMISSION (SI PAS DÉJÀ FAIT)

```bash
# Vérifier si Spatie est installé
php artisan tinker --execute="echo Schema::hasTable('roles') ? '✅ Déjà installé' : '❌ Pas installé';"

# Si pas installé, exécuter:
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 4. CRÉER RÔLES ET PERMISSIONS

```bash
# Exécuter le seeder Spatie
php artisan db:seed --class=RolesAndPermissionsSeeder

# Vérifier
php artisan tinker --execute="echo 'Rôles: ' . \Spatie\Permission\Models\Role::count();"
```

### 5. CRÉER COMPTE ADMINISTRATEUR

```bash
# Créer admin@kaypa.com
php artisan db:seed --class=AdminUserSeeder

# Vérifier
php artisan tinker --execute="\$admin = \App\Models\User::where('email', 'admin@kaypa.com')->first(); echo \$admin ? '✅ Admin créé' : '❌ Erreur';"
```

**Identifiants admin:**
- Email: `admin@kaypa.com`
- Mot de passe: `password123`

### 6. VIDER LES CACHES

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 7. TESTER L'APPLICATION

1. Se connecter avec `admin@kaypa.com` / `password123`
2. Vérifier l'accès aux nouvelles sections:
   - Gestion Financière (`/fund-movements`)
   - Rôles et Permissions (`/roles-permissions`)
3. **CHANGER LE MOT DE PASSE ADMIN IMMÉDIATEMENT**

---

## 👥 ASSIGNER RÔLES AUX UTILISATEURS EXISTANTS

Une fois connecté en tant qu'admin:

1. Aller dans **Utilisateurs** (`/users`)
2. Cliquer sur chaque utilisateur
3. Assigner le rôle approprié:
   - **admin**: Accès complet
   - **manager**: Gestion + approbations
   - **agent**: Opérations quotidiennes
   - **comptable**: Rapports uniquement
   - **viewer**: Lecture seule

---

## 🆘 EN CAS DE PROBLÈME

### Si une migration échoue:

```bash
# Vérifier les tables existantes
php artisan tinker --execute="print_r(DB::select('SHOW TABLES'));"

# Vérifier les migrations exécutées
php artisan tinker --execute="print_r(DB::table('migrations')->get());"
```

### Restaurer le backup:

```bash
mysql -u root -p mybankkaypa < backup_kaypa_YYYYMMDD_HHMMSS.sql
```

### Vérifier les erreurs:

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Vérifier les permissions fichiers
ls -la storage/
```

---

## ✅ VÉRIFICATIONS POST-DÉPLOIEMENT

- [ ] Table `fund_movements` existe
- [ ] Tables Spatie (`roles`, `permissions`, etc.) existent
- [ ] Admin `admin@kaypa.com` peut se connecter
- [ ] Navigation affiche "Gestion Financière"
- [ ] Utilisateurs existants visibles dans `/users`
- [ ] Mot de passe admin changé
- [ ] Backup sauvegardé en lieu sûr

---

## 📞 SUPPORT

En cas de problème, contacter l'équipe technique avec:
1. Message d'erreur complet
2. Logs Laravel (`storage/logs/laravel.log`)
3. Version PHP: `php -v`
4. Version Laravel: `php artisan --version`

---

## 🔄 SCRIPT AUTOMATIQUE

Pour un déploiement automatique (Windows PowerShell):

```powershell
.\deploy-production.ps1
```

Pour Linux/Mac:

```bash
chmod +x deploy-production.sh
./deploy-production.sh
```
