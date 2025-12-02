# Script de déploiement production KAYPA
# À exécuter sur le serveur de production

Write-Host "🚀 DÉPLOIEMENT KAYPA - PRODUCTION" -ForegroundColor Green
Write-Host "=================================" -ForegroundColor Green
Write-Host ""

# 1. Backup base de données
Write-Host "📦 1. BACKUP BASE DE DONNÉES..." -ForegroundColor Yellow
$backupDate = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFile = "backup_kaypa_$backupDate.sql"
mysqldump -u root mybankkaypa > $backupFile
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Backup créé: $backupFile" -ForegroundColor Green
} else {
    Write-Host "   ❌ Erreur backup!" -ForegroundColor Red
    exit 1
}
Write-Host ""

# 2. Migration fund_movements
Write-Host "🗄️  2. AJOUT TABLE FUND_MOVEMENTS..." -ForegroundColor Yellow
php artisan migrate --path=database/migrations/production
Write-Host ""

# 3. Installation Spatie Permission (si pas déjà fait)
Write-Host "🔐 3. VÉRIFICATION SPATIE PERMISSION..." -ForegroundColor Yellow
$spatieInstalled = php artisan tinker --execute="echo Schema::hasTable('roles') ? 'yes' : 'no';" 2>$null
if ($spatieInstalled -match "no") {
    Write-Host "   Installation de Spatie Permission..." -ForegroundColor Cyan
    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    php artisan migrate
    Write-Host "   ✅ Tables Spatie créées" -ForegroundColor Green
} else {
    Write-Host "   ✅ Tables Spatie déjà présentes" -ForegroundColor Green
}
Write-Host ""

# 4. Seeder Rôles et Permissions
Write-Host "👥 4. CONFIGURATION RÔLES & PERMISSIONS..." -ForegroundColor Yellow
php artisan db:seed --class=RolesAndPermissionsSeeder
Write-Host ""

# 5. Création admin
Write-Host "🔑 5. CRÉATION COMPTE ADMINISTRATEUR..." -ForegroundColor Yellow
php artisan db:seed --class=AdminUserSeeder
Write-Host ""

# 6. Vider les caches
Write-Host "🧹 6. NETTOYAGE CACHES..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
Write-Host "   ✅ Caches vidés" -ForegroundColor Green
Write-Host ""

# 7. Résumé
Write-Host "=================================" -ForegroundColor Green
Write-Host "✅ DÉPLOIEMENT TERMINÉ!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 INFORMATIONS DE CONNEXION:" -ForegroundColor Cyan
Write-Host "   Email: admin@kaypa.com" -ForegroundColor White
Write-Host "   Mot de passe: password123" -ForegroundColor White
Write-Host ""
Write-Host "⚠️  IMPORTANT:" -ForegroundColor Yellow
Write-Host "   1. Changez le mot de passe admin immédiatement" -ForegroundColor White
Write-Host "   2. Connectez-vous et assignez les rôles aux utilisateurs existants" -ForegroundColor White
Write-Host "   3. Sauvegarde créée: $backupFile" -ForegroundColor White
Write-Host ""
