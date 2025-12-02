#!/bin/bash

# Script de déploiement production KAYPA (Linux/Mac)
# À exécuter sur le serveur de production

echo "🚀 DÉPLOIEMENT KAYPA - PRODUCTION"
echo "================================="
echo ""

# 1. Backup base de données
echo "📦 1. BACKUP BASE DE DONNÉES..."
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="backup_kaypa_$BACKUP_DATE.sql"
mysqldump -u root -p mybankkaypa > $BACKUP_FILE
if [ $? -eq 0 ]; then
    echo "   ✅ Backup créé: $BACKUP_FILE"
else
    echo "   ❌ Erreur backup!"
    exit 1
fi
echo ""

# 2. Migration fund_movements
echo "🗄️  2. AJOUT TABLE FUND_MOVEMENTS..."
php artisan migrate --path=database/migrations/production
echo ""

# 3. Installation Spatie Permission (si pas déjà fait)
echo "🔐 3. VÉRIFICATION SPATIE PERMISSION..."
SPATIE_INSTALLED=$(php artisan tinker --execute="echo Schema::hasTable('roles') ? 'yes' : 'no';" 2>/dev/null)
if [[ $SPATIE_INSTALLED == *"no"* ]]; then
    echo "   Installation de Spatie Permission..."
    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    php artisan migrate
    echo "   ✅ Tables Spatie créées"
else
    echo "   ✅ Tables Spatie déjà présentes"
fi
echo ""

# 4. Seeder Rôles et Permissions
echo "👥 4. CONFIGURATION RÔLES & PERMISSIONS..."
php artisan db:seed --class=RolesAndPermissionsSeeder
echo ""

# 5. Création admin
echo "🔑 5. CRÉATION COMPTE ADMINISTRATEUR..."
php artisan db:seed --class=AdminUserSeeder
echo ""

# 6. Vider les caches
echo "🧹 6. NETTOYAGE CACHES..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "   ✅ Caches vidés"
echo ""

# 7. Résumé
echo "================================="
echo "✅ DÉPLOIEMENT TERMINÉ!"
echo ""
echo "📋 INFORMATIONS DE CONNEXION:"
echo "   Email: admin@kaypa.com"
echo "   Mot de passe: password123"
echo ""
echo "⚠️  IMPORTANT:"
echo "   1. Changez le mot de passe admin immédiatement"
echo "   2. Connectez-vous et assignez les rôles aux utilisateurs existants"
echo "   3. Sauvegarde créée: $BACKUP_FILE"
echo ""
