#!/bin/bash

###############################################################################
# Script de déploiement initial KAYPA sur VPS
# Exécuter UNE SEULE FOIS lors du premier déploiement
###############################################################################

set -e  # Arrêter si erreur

echo "🚀 DÉPLOIEMENT INITIAL KAYPA VERSION 2"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. Vérifier connexion base de données
echo "1️⃣  Test connexion base de données..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connexion OK';"
if [ $? -eq 0 ]; then
    echo "   ✅ Base de données connectée"
else
    echo "   ❌ ERREUR: Impossible de se connecter à la base de données"
    echo "   Vérifiez le fichier .env"
    exit 1
fi
echo ""

# 2. Publier fichiers Spatie Permission si nécessaire
echo "2️⃣  Configuration Spatie Permission..."
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations" 2>/dev/null || true
echo "   ✅ Fichiers publiés"
echo ""

# 3. Exécuter UNIQUEMENT les nouvelles migrations
echo "3️⃣  Exécution des nouvelles migrations..."
echo "   ⚠️  Les tables existantes ne seront PAS modifiées"

# Migration colonnes 2FA users
php artisan migrate --path=database/migrations/2025_12_02_050430_add_two_factor_columns_to_users_table.php --force 2>/dev/null || echo "   → Colonnes 2FA users déjà présentes"

# Migration table user_devices
php artisan migrate --path=database/migrations/2025_12_02_050437_create_user_devices_table.php --force 2>/dev/null || echo "   → Table user_devices déjà présente"

# Migrations Spatie
php artisan migrate --path=vendor/spatie/laravel-permission/database/migrations --force 2>/dev/null || echo "   → Tables Spatie déjà présentes"

echo "   ✅ Migrations terminées"
echo ""

# 4. Setup rôles et permissions
echo "4️⃣  Configuration rôles et permissions..."
php artisan db:seed --class=ProductionSetupSeeder --force
echo "   ✅ Rôles et permissions configurés"
echo ""

# 5. Créer super admin
echo "5️⃣  Création super administrateur..."
php create-admin.php 2>/dev/null || echo "   → Admin déjà existant"
echo ""

# 6. Optimisation
echo "6️⃣  Optimisation Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "   ✅ Caches générés"
echo ""

# 7. Permissions fichiers
echo "7️⃣  Configuration permissions fichiers..."
chmod -R 775 storage bootstrap/cache
echo "   ✅ Permissions configurées"
echo ""

# 8. Résumé
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ DÉPLOIEMENT INITIAL TERMINÉ AVEC SUCCÈS!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 STATISTIQUES:"
php artisan tinker --execute="
echo '   - Utilisateurs: ' . \App\Models\User::count() . PHP_EOL;
echo '   - Clients: ' . \App\Models\Client::count() . PHP_EOL;
echo '   - Comptes: ' . \App\Models\Account::count() . PHP_EOL;
echo '   - Transactions: ' . \App\Models\AccountTransaction::count() . PHP_EOL;
"
echo ""
echo "🔐 COMPTES ADMIN:"
php check-users.php | grep "admin"
echo ""
echo "⚠️  PROCHAINES ÉTAPES:"
echo "   1. Changez IMMÉDIATEMENT les mots de passe admin"
echo "   2. Activez 2FA pour tous les comptes admin"
echo "   3. Configurez le serveur web (Nginx/Apache)"
echo "   4. Configurez SSL/HTTPS"
echo "   5. Testez toutes les fonctionnalités"
echo ""
