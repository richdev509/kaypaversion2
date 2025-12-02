#!/bin/bash

###############################################################################
# Script de rollback KAYPA
# Exécuter en cas de problème après une mise à jour
###############################################################################

set -e

echo "⚠️  ROLLBACK KAYPA VERSION 2"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. Mode maintenance
echo "1️⃣  Activation mode maintenance..."
php artisan down
echo "   ✅ Site en maintenance"
echo ""

# 2. Rollback Git
echo "2️⃣  Retour version précédente..."
read -p "   Nombre de commits à annuler (défaut: 1): " COMMITS
COMMITS=${COMMITS:-1}
git reset --hard HEAD~$COMMITS
echo "   ✅ Code restauré"
echo ""

# 3. Rollback migrations
echo "3️⃣  Annulation dernières migrations..."
read -p "   Nombre de migrations à annuler (défaut: 1): " MIGRATIONS
MIGRATIONS=${MIGRATIONS:-1}
php artisan migrate:rollback --step=$MIGRATIONS --force
echo "   ✅ Base de données restaurée"
echo ""

# 4. Réinstaller dépendances
echo "4️⃣  Réinstallation dépendances..."
composer install --no-dev --optimize-autoloader
echo "   ✅ Dépendances installées"
echo ""

# 5. Optimisation
echo "5️⃣  Régénération caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "   ✅ Caches régénérés"
echo ""

# 6. Redémarrage
echo "6️⃣  Redémarrage services..."
php artisan queue:restart 2>/dev/null || true
php artisan up
echo "   ✅ Site restauré"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ ROLLBACK TERMINÉ"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
