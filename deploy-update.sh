#!/bin/bash

###############################################################################
# Script de mise à jour KAYPA sur VPS
# Exécuter à chaque nouvelle version/mise à jour du code
###############################################################################

set -e

echo "🔄 MISE À JOUR KAYPA VERSION 2"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. Mode maintenance
echo "1️⃣  Activation mode maintenance..."
php artisan down --retry=60
echo "   ✅ Site en maintenance"
echo ""

# 2. Pull dernières modifications
echo "2️⃣  Récupération dernières modifications..."
git pull origin main
echo "   ✅ Code à jour"
echo ""

# 3. Composer
echo "3️⃣  Installation dépendances Composer..."
composer install --no-dev --optimize-autoloader
echo "   ✅ Dépendances installées"
echo ""

# 4. Migrations
echo "4️⃣  Exécution migrations..."
php artisan migrate --force
echo "   ✅ Base de données à jour"
echo ""

# 5. Vider et recréer caches
echo "5️⃣  Optimisation caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "   ✅ Caches optimisés"
echo ""

# 6. Permissions
echo "6️⃣  Vérification permissions..."
chmod -R 775 storage bootstrap/cache
echo "   ✅ Permissions OK"
echo ""

# 7. Redémarrage services
echo "7️⃣  Redémarrage services..."
php artisan queue:restart 2>/dev/null || echo "   → Pas de queue"
echo "   ✅ Services redémarrés"
echo ""

# 8. Désactiver mode maintenance
echo "8️⃣  Désactivation mode maintenance..."
php artisan up
echo "   ✅ Site en ligne"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ MISE À JOUR TERMINÉE AVEC SUCCÈS!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🕐 $(date)"
echo ""
