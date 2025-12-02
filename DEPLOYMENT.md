# 🚀 Scripts de Déploiement KAYPA Version 2

Scripts pour déployer et maintenir l'application sur VPS de production.

## 📋 Scripts Disponibles

### 1. `deploy-initial.sh` - Déploiement Initial
**Utilisation :** Une seule fois lors du premier déploiement

```bash
chmod +x deploy-initial.sh
./deploy-initial.sh
```

**Ce qu'il fait :**
- ✅ Vérifie connexion base de données
- ✅ Publie fichiers Spatie Permission
- ✅ Exécute nouvelles migrations (2FA, user_devices)
- ✅ Configure rôles et permissions
- ✅ Crée super admin
- ✅ Optimise caches Laravel
- ✅ Configure permissions fichiers

### 2. `deploy-update.sh` - Mise à Jour
**Utilisation :** À chaque nouvelle version

```bash
chmod +x deploy-update.sh
./deploy-update.sh
```

**Ce qu'il fait :**
- ✅ Active mode maintenance
- ✅ Pull dernières modifications Git
- ✅ Installe dépendances Composer
- ✅ Exécute migrations
- ✅ Régénère caches
- ✅ Redémarre services
- ✅ Désactive mode maintenance

### 3. `rollback.sh` - Annulation
**Utilisation :** En cas de problème après mise à jour

```bash
chmod +x rollback.sh
./rollback.sh
```

**Ce qu'il fait :**
- ⚠️ Active mode maintenance
- ⚠️ Annule commits Git
- ⚠️ Rollback migrations
- ⚠️ Réinstalle dépendances
- ⚠️ Régénère caches
- ⚠️ Restaure le site

### 4. `create-admin.php` - Créer Admin
**Utilisation :** Créer un nouveau compte administrateur

```bash
php create-admin.php
```

**Identifiants créés :**
- 📧 Email: `superadmin@kaypa.ht`
- 🔑 Mot de passe: `SuperAdmin@2024!`

### 5. `check-users.php` - Vérifier Users
**Utilisation :** Lister utilisateurs et rôles

```bash
php check-users.php
```

## 🔧 Configuration Requise

### Fichier `.env` Production

```env
APP_NAME=KAYPA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mybankkaypa
DB_USERNAME=votre_user
DB_PASSWORD=votre_password

# ... autres configs
```

### Permissions Serveur

```bash
# Propriétaire des fichiers
chown -R www-data:www-data /path/to/kaypaversion2

# Permissions stockage
chmod -R 775 storage bootstrap/cache
```

## 📦 Workflow de Déploiement

### Premier Déploiement

```bash
# 1. Cloner le projet
git clone https://github.com/richdev509/kaypaversion2.git
cd kaypaversion2

# 2. Configurer .env
cp .env.example .env
nano .env  # Éditer avec infos production

# 3. Installer dépendances
composer install --no-dev --optimize-autoloader

# 4. Générer clé application
php artisan key:generate

# 5. Exécuter déploiement initial
chmod +x deploy-initial.sh
./deploy-initial.sh

# 6. Configurer serveur web (Nginx/Apache)
# 7. Configurer SSL
# 8. Tester application
```

### Mise à Jour Régulière

```bash
cd /path/to/kaypaversion2
./deploy-update.sh
```

### En Cas de Problème

```bash
cd /path/to/kaypaversion2
./rollback.sh
```

## ⚠️ Points Importants

### Avant Premier Déploiement
- [ ] Configurer `.env` avec bonnes credentials DB
- [ ] Vérifier connexion base de données existante
- [ ] Faire backup base de données
- [ ] Tester en local d'abord

### Après Premier Déploiement
- [ ] Changer IMMÉDIATEMENT mot de passe admin
- [ ] Activer 2FA pour comptes admin
- [ ] Tester toutes fonctionnalités
- [ ] Configurer certificat SSL
- [ ] Configurer cron jobs si nécessaire

### Sécurité
- [ ] Ne jamais commit `.env` dans Git
- [ ] Utiliser HTTPS obligatoirement
- [ ] Activer firewall (UFW)
- [ ] Limiter accès SSH par clé
- [ ] Activer 2FA pour tous admins

## 🆘 Dépannage

### Erreur connexion DB
```bash
php artisan tinker --execute="DB::connection()->getPdo();"
```

### Problème permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Vider tous les caches
```bash
php artisan optimize:clear
```

### Voir logs erreurs
```bash
tail -f storage/logs/laravel.log
```

## 📞 Support

En cas de problème, vérifier :
1. Logs Laravel : `storage/logs/laravel.log`
2. Logs Nginx/Apache : `/var/log/nginx/error.log`
3. Permissions fichiers
4. Configuration `.env`
5. Connexion base de données

---

**Version :** 2.0  
**Date :** Décembre 2025  
**Projet :** KAYPA - Système de gestion d'épargne
