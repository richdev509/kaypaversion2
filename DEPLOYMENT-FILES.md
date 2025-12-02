# 📦 Fichiers de Déploiement KAYPA

Liste complète des fichiers créés pour le déploiement en production.

## ✅ Scripts à Uploader sur le VPS

### Scripts Shell (Linux)
1. **`deploy-initial.sh`** - Premier déploiement (une seule fois)
2. **`deploy-update.sh`** - Mises à jour régulières
3. **`rollback.sh`** - Annulation en cas de problème

### Scripts PHP
4. **`create-admin.php`** - Créer super admin
5. **`check-users.php`** - Vérifier utilisateurs et rôles
6. **`deploy.php`** - Script alternatif déploiement (Windows/Linux)

### Seeder
7. **`database/seeders/ProductionSetupSeeder.php`** - Configuration rôles/permissions

### Documentation
8. **`DEPLOYMENT.md`** - Guide complet déploiement
9. **`VPS-SETUP.md`** - Guide rapide installation VPS
10. **`DEPLOYMENT-FILES.md`** - Ce fichier

## 📋 Checklist Avant Déploiement

### Sur le serveur local (développement)
- [ ] Tester tous les scripts localement
- [ ] Vérifier que Git est à jour
- [ ] Commit et push tous changements
- [ ] Faire backup base de données locale

### Sur le VPS (production)
- [ ] Faire backup complet base de données existante
- [ ] Vérifier connexion SSH
- [ ] Vérifier espace disque disponible
- [ ] Noter credentials admin actuels

## 🔄 Ordre d'Exécution Premier Déploiement

```bash
# 1. Configuration initiale système (une fois)
apt update && apt upgrade -y
apt install nginx php8.2-fpm mysql-server composer git

# 2. Cloner projet
cd /var/www
git clone https://github.com/richdev509/kaypaversion2.git
cd kaypaversion2

# 3. Configuration
cp .env.example .env
nano .env  # Éditer credentials

# 4. Dépendances
composer install --no-dev --optimize-autoloader

# 5. Clé application
php artisan key:generate

# 6. Rendre exécutables
chmod +x deploy-initial.sh deploy-update.sh rollback.sh

# 7. DÉPLOYER
./deploy-initial.sh

# 8. Configurer Nginx (voir VPS-SETUP.md)
# 9. Configurer SSL
# 10. Tester application
```

## 📊 Ce que fait deploy-initial.sh

1. ✅ Test connexion DB
2. ✅ Publie migrations Spatie
3. ✅ Migre colonnes 2FA (users)
4. ✅ Migre table user_devices
5. ✅ Migre tables Spatie (roles, permissions, etc.)
6. ✅ Crée 25 permissions
7. ✅ Crée 5 rôles (admin, manager, comptable, agent, support)
8. ✅ Assigne permissions aux rôles
9. ✅ Crée super admin
10. ✅ Génère caches optimisés
11. ✅ Configure permissions fichiers
12. ✅ Affiche statistiques

## 🔐 Comptes Créés Automatiquement

### Super Admin (nouveau)
- **Email:** superadmin@kaypa.ht
- **Mot de passe:** SuperAdmin@2024!
- **Rôle:** admin
- **Permissions:** Toutes

### Admins Existants (préservés)
- Les comptes existants ne sont PAS modifiés
- Leurs données et permissions sont conservées
- Possibilité d'assigner rôles manuellement après

## 🚨 Sécurité Post-Déploiement

**À faire IMMÉDIATEMENT après premier déploiement :**

1. **Changer mots de passe admin**
   ```bash
   # Via interface web ou:
   php artisan tinker
   >>> $user = User::where('email', 'superadmin@kaypa.ht')->first();
   >>> $user->password = Hash::make('NouveauMotDePasseTresFort!');
   >>> $user->save();
   ```

2. **Activer 2FA**
   - Se connecter avec compte admin
   - Aller dans Profil
   - Section "🔐 Authentification à Deux Facteurs"
   - Cliquer "Activer"
   - Scanner QR Code avec Google Authenticator
   - Sauvegarder codes de récupération

3. **Vérifier permissions fichiers**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data /var/www/kaypaversion2
   ```

4. **Configurer firewall**
   ```bash
   ufw allow 22/tcp    # SSH
   ufw allow 80/tcp    # HTTP
   ufw allow 443/tcp   # HTTPS
   ufw enable
   ```

5. **Désactiver débogage**
   ```env
   # .env
   APP_DEBUG=false
   APP_ENV=production
   ```

## 📝 Logs Important

Vérifier ces logs après déploiement :

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs Nginx
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# Logs PHP-FPM
tail -f /var/log/php8.2-fpm.log
```

## 🔄 Workflow Mises à Jour

```bash
# Développement local
git add .
git commit -m "Nouvelles fonctionnalités"
git push origin main

# VPS Production
cd /var/www/kaypaversion2
./deploy-update.sh  # Fait tout automatiquement
```

## 💾 Backups

**Avant CHAQUE mise à jour :**

```bash
# Backup DB
mysqldump -u user -p mybankkaypa > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup fichiers
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/kaypaversion2
```

## 📞 Support

**En cas de problème :**

1. Vérifier logs (voir section Logs)
2. Vérifier permissions fichiers
3. Vérifier configuration .env
4. Tester connexion DB
5. Exécuter `php artisan optimize:clear`
6. Si nécessaire : `./rollback.sh`

**Contacts :**
- Documentation complète : `DEPLOYMENT.md`
- Setup rapide : `VPS-SETUP.md`

---

**Date création :** Décembre 2025  
**Version système :** KAYPA v2.0  
**Testé sur :** Ubuntu 22.04, PHP 8.2, MySQL 8.0
