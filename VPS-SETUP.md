# 🎯 Guide Rapide Déploiement VPS

## 📥 Étape 1 : Connexion et Préparation

```bash
# Se connecter au VPS
ssh root@votre-ip

# Mettre à jour système
apt update && apt upgrade -y

# Installer dépendances
apt install -y nginx php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd git composer mysql-server
```

## 📂 Étape 2 : Cloner Projet

```bash
# Aller dans répertoire web
cd /var/www

# Cloner projet
git clone https://github.com/richdev509/kaypaversion2.git
cd kaypaversion2

# Permissions propriétaire
chown -R www-data:www-data /var/www/kaypaversion2
chmod -R 775 storage bootstrap/cache
```

## ⚙️ Étape 3 : Configuration

```bash
# Copier et éditer .env
cp .env.example .env
nano .env
```

**Éditer dans `.env` :**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_DATABASE=mybankkaypa
DB_USERNAME=votre_user_db
DB_PASSWORD=votre_password_db
```

## 🚀 Étape 4 : Déploiement Initial

```bash
# Installer dépendances
composer install --no-dev --optimize-autoloader

# Générer clé
php artisan key:generate

# Rendre scripts exécutables
chmod +x deploy-initial.sh deploy-update.sh rollback.sh

# DÉPLOYER !
./deploy-initial.sh
```

**Identifiants créés automatiquement :**
- 📧 Email: `superadmin@kaypa.ht`
- 🔑 Mot de passe: `SuperAdmin@2024!`

## 🌐 Étape 5 : Configurer Nginx

```bash
nano /etc/nginx/sites-available/kaypa
```

**Contenu :**
```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /var/www/kaypaversion2/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Activer site :**
```bash
ln -s /etc/nginx/sites-available/kaypa /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

## 🔒 Étape 6 : SSL (Certbot)

```bash
# Installer Certbot
apt install -y certbot python3-certbot-nginx

# Obtenir certificat
certbot --nginx -d votre-domaine.com

# Auto-renouvellement (test)
certbot renew --dry-run
```

## ✅ Étape 7 : Vérification

```bash
# Tester connexion DB
php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';"

# Voir utilisateurs
php check-users.php

# Vérifier site
curl http://votre-domaine.com
```

## 🔄 Mises à Jour Futures

```bash
cd /var/www/kaypaversion2
git pull origin main
./deploy-update.sh
```

## 🆘 Dépannage Rapide

**Erreur 500 :**
```bash
tail -f storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage
```

**Problème DB :**
```bash
php artisan tinker --execute="DB::connection()->getPdo();"
```

**Vider caches :**
```bash
php artisan optimize:clear
```

---

**Temps total installation :** ~15-20 minutes  
**Support :** Vérifier `DEPLOYMENT.md` pour détails complets
