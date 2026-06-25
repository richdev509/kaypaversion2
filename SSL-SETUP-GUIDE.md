# 🔐 Guide Configuration SSL avec Let's Encrypt
# Pour le domaine: myapp.mykaypa.com

## 📋 Prérequis

1. **Serveur VPS Ubuntu/Debian**
2. **Apache2 installé**
3. **Domaine configuré** (DNS pointant vers votre serveur)
4. **Accès root ou sudo**

---

## 🚀 Installation et Configuration Complète

### Étape 1: Installer Apache et modules nécessaires

```bash
# Mettre à jour le système
sudo apt update
sudo apt upgrade -y

# Installer Apache et PHP
sudo apt install -y apache2 php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-bcmath php8.1-curl php8.1-zip php8.1-gd

# Activer les modules Apache nécessaires
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod proxy_fcgi
sudo a2enconf php8.1-fpm

# Redémarrer Apache
sudo systemctl restart apache2
```

---

### Étape 2: Déployer votre application Laravel

```bash
# Aller dans le répertoire web
cd /var/www/

# Cloner votre projet (ou transférer via FTP/SCP)
sudo git clone https://github.com/richdev509/kaypaversion2.git
cd kaypaversion2

# Installer les dépendances Composer
sudo composer install --no-dev --optimize-autoloader

# Configurer les permissions
sudo chown -R www-data:www-data /var/www/kaypaversion2
sudo chmod -R 755 /var/www/kaypaversion2
sudo chmod -R 775 /var/www/kaypaversion2/storage
sudo chmod -R 775 /var/www/kaypaversion2/bootstrap/cache

# Copier et configurer .env
sudo cp .env.example .env
sudo nano .env

# Générer la clé d'application
sudo php artisan key:generate

# Exécuter le script de déploiement intelligent
sudo php deploy-smart.php
```

---

### Étape 3: Configurer le fichier .env pour production

```bash
sudo nano /var/www/kaypaversion2/.env
```

Configuration recommandée:

```env
APP_NAME="KAYPA"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_GENEREE
APP_DEBUG=false
APP_URL=https://myapp.mykaypa.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mybankkaypa
DB_USERNAME=kaypa_user
DB_PASSWORD=MOT_DE_PASSE_SECURISE

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Sécurité
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

---

### Étape 4: Copier la configuration Apache

```bash
# Copier le fichier de configuration
sudo cp /var/www/kaypaversion2/myapp.mykaypa.com.conf /etc/apache2/sites-available/

# Vérifier la configuration
cat /etc/apache2/sites-available/myapp.mykaypa.com.conf
```

---

### Étape 5: Installer Certbot pour Let's Encrypt

```bash
# Installer Certbot
sudo apt install -y certbot python3-certbot-apache

# Vérifier l'installation
certbot --version
```

---

### Étape 6: Obtenir le certificat SSL avec Certbot

```bash
# Option 1: Automatique (recommandé)
sudo certbot --apache -d myapp.mykaypa.com -d www.myapp.mykaypa.com

# Suivre les instructions:
# 1. Entrer votre email
# 2. Accepter les conditions
# 3. Choisir de rediriger HTTP vers HTTPS (option 2)

# Option 2: Certificat seulement (configuration manuelle)
sudo certbot certonly --apache -d myapp.mykaypa.com -d www.myapp.mykaypa.com
```

**Réponses aux questions de Certbot:**

```
Email: votre-email@example.com
Terms of Service: (A)gree
Share email: (N)o
Which domain: Appuyez sur Entrée pour tous
```

---

### Étape 7: Activer la configuration du site

```bash
# Désactiver le site par défaut
sudo a2dissite 000-default.conf
sudo a2dissite default-ssl.conf

# Activer votre site
sudo a2ensite myapp.mykaypa.com.conf

# Tester la configuration Apache
sudo apache2ctl configtest

# Si "Syntax OK", redémarrer Apache
sudo systemctl restart apache2
```

---

### Étape 8: Vérifier le fonctionnement

```bash
# Vérifier le statut Apache
sudo systemctl status apache2

# Vérifier les certificats SSL
sudo certbot certificates

# Tester l'accès
curl -I https://myapp.mykaypa.com
```

---

## 🔄 Renouvellement automatique du certificat SSL

Les certificats Let's Encrypt expirent après 90 jours. Certbot configure automatiquement le renouvellement.

### Vérifier le renouvellement automatique:

```bash
# Vérifier le timer de renouvellement
sudo systemctl status certbot.timer

# Tester le renouvellement (mode dry-run)
sudo certbot renew --dry-run

# Forcer le renouvellement si nécessaire
sudo certbot renew --force-renewal
```

### Configurer le renouvellement manuel (si nécessaire):

```bash
# Éditer le crontab
sudo crontab -e

# Ajouter cette ligne pour vérifier le renouvellement 2 fois par jour
0 0,12 * * * certbot renew --quiet --post-hook "systemctl reload apache2"
```

---

## 🛡️ Vérification de la sécurité SSL

### Tester votre configuration SSL:

1. **SSL Labs**: https://www.ssllabs.com/ssltest/
   - Entrez: `myapp.mykaypa.com`
   - Objectif: Note A ou A+

2. **Vérification en ligne de commande**:
```bash
# Tester le certificat
openssl s_client -connect myapp.mykaypa.com:443 -servername myapp.mykaypa.com

# Vérifier les headers de sécurité
curl -I https://myapp.mykaypa.com
```

---

## 🔥 Configuration du pare-feu (UFW)

```bash
# Installer UFW si nécessaire
sudo apt install -y ufw

# Autoriser SSH (IMPORTANT! avant d'activer UFW)
sudo ufw allow 22/tcp

# Autoriser HTTP et HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Activer le pare-feu
sudo ufw enable

# Vérifier le statut
sudo ufw status
```

---

## 📊 Optimisations Laravel en production

```bash
# Se placer dans le répertoire du projet
cd /var/www/kaypaversion2

# Optimiser l'autoloader
sudo composer install --optimize-autoloader --no-dev

# Mettre en cache la configuration
sudo php artisan config:cache

# Mettre en cache les routes
sudo php artisan route:cache

# Mettre en cache les vues
sudo php artisan view:cache

# Optimiser le chargement
sudo php artisan optimize
```

---

## 🔍 Dépannage

### Problème: "Connection refused"

```bash
# Vérifier qu'Apache écoute sur le port 443
sudo netstat -tulpn | grep :443

# Vérifier les logs Apache
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/myapp.mykaypa.com-ssl-error.log
```

### Problème: "Certificate not found"

```bash
# Vérifier les certificats
ls -la /etc/letsencrypt/live/myapp.mykaypa.com/

# Regénérer si nécessaire
sudo certbot --apache -d myapp.mykaypa.com --force-renewal
```

### Problème: "Permission denied"

```bash
# Corriger les permissions
sudo chown -R www-data:www-data /var/www/kaypaversion2
sudo chmod -R 755 /var/www/kaypaversion2
sudo chmod -R 775 /var/www/kaypaversion2/storage
sudo chmod -R 775 /var/www/kaypaversion2/bootstrap/cache
```

### Problème: "500 Internal Server Error"

```bash
# Vérifier les logs Laravel
sudo tail -f /var/www/kaypaversion2/storage/logs/laravel.log

# Vérifier les permissions storage
sudo chmod -R 775 /var/www/kaypaversion2/storage
sudo chown -R www-data:www-data /var/www/kaypaversion2/storage

# Nettoyer les caches
cd /var/www/kaypaversion2
sudo php artisan cache:clear
sudo php artisan config:clear
sudo php artisan route:clear
sudo php artisan view:clear
```

---

## 📝 Checklist finale

- [ ] Apache installé et fonctionnel
- [ ] Modules Apache activés (rewrite, ssl, headers)
- [ ] Application déployée dans `/var/www/kaypaversion2`
- [ ] Permissions correctes (www-data:www-data)
- [ ] Fichier .env configuré pour production
- [ ] Configuration Apache copiée et activée
- [ ] Certificat SSL obtenu avec Certbot
- [ ] Site accessible via HTTPS
- [ ] Redirection HTTP → HTTPS fonctionnelle
- [ ] Test SSL Labs réussi (Note A ou A+)
- [ ] Renouvellement automatique SSL configuré
- [ ] Pare-feu UFW configuré
- [ ] Caches Laravel optimisés
- [ ] Logs vérifiés et accessibles

---

## 🎯 Commandes utiles

```bash
# Redémarrer Apache
sudo systemctl restart apache2

# Recharger la configuration Apache (sans downtime)
sudo systemctl reload apache2

# Vérifier la configuration Apache
sudo apache2ctl configtest

# Voir les sites activés
ls -la /etc/apache2/sites-enabled/

# Voir les logs en temps réel
sudo tail -f /var/log/apache2/myapp.mykaypa.com-ssl-error.log

# Vérifier l'utilisation des ressources
htop
df -h
free -m
```

---

## 📞 Support

En cas de problème:
1. Vérifiez les logs Apache: `/var/log/apache2/`
2. Vérifiez les logs Laravel: `/var/www/kaypaversion2/storage/logs/`
3. Testez la configuration: `sudo apache2ctl configtest`
4. Vérifiez les certificats: `sudo certbot certificates`

---

## ✅ Votre application est maintenant en ligne!

🌐 **URL**: https://myapp.mykaypa.com

🔐 **Admin**: admin@kaypa.ht / Admin@2024!

⚠️ **N'oubliez pas de changer le mot de passe admin!**

🎉 **Félicitations!**
