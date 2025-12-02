# Script de déploiement intelligent KAYPA
# Analyse et répare automatiquement la base de données

param(
    [switch]$Help
)

if ($Help) {
    Write-Host ""
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
    Write-Host "  DÉPLOIEMENT INTELLIGENT KAYPA - AIDE" -ForegroundColor Cyan
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "USAGE:" -ForegroundColor Yellow
    Write-Host "  .\deploy-smart.ps1" -ForegroundColor White
    Write-Host ""
    Write-Host "DESCRIPTION:" -ForegroundColor Yellow
    Write-Host "  Ce script analyse automatiquement votre base de données et" -ForegroundColor White
    Write-Host "  applique uniquement les modifications nécessaires sans" -ForegroundColor White
    Write-Host "  perdre aucune donnée existante." -ForegroundColor White
    Write-Host ""
    Write-Host "FONCTIONNALITÉS:" -ForegroundColor Yellow
    Write-Host "  ✓ Détecte les tables manquantes" -ForegroundColor Green
    Write-Host "  ✓ Détecte les colonnes manquantes" -ForegroundColor Green
    Write-Host "  ✓ Synchronise automatiquement les migrations" -ForegroundColor Green
    Write-Host "  ✓ Préserve toutes les données existantes" -ForegroundColor Green
    Write-Host "  ✓ Peut être exécuté plusieurs fois sans problème" -ForegroundColor Green
    Write-Host ""
    Write-Host "EXEMPLES:" -ForegroundColor Yellow
    Write-Host "  .\deploy-smart.ps1          # Exécution normale" -ForegroundColor White
    Write-Host "  .\deploy-smart.ps1 -Help    # Afficher cette aide" -ForegroundColor White
    Write-Host ""
    exit 0
}

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║     🚀 DÉPLOIEMENT INTELLIGENT KAYPA VERSION 2           ║" -ForegroundColor Cyan
Write-Host "║     Analyse et réparation automatique de la base         ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Vérifier que nous sommes dans le bon répertoire
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Erreur: Fichier artisan non trouvé!" -ForegroundColor Red
    Write-Host "   Veuillez exécuter ce script depuis la racine du projet Laravel." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "   Répertoire actuel: $PWD" -ForegroundColor Gray
    Write-Host ""
    exit 1
}

# Vérifier que PHP est disponible
try {
    $phpVersion = php -v 2>&1 | Select-String "PHP" | Select-Object -First 1
    Write-Host "✓ PHP détecté: $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur: PHP n'est pas installé ou accessible!" -ForegroundColor Red
    Write-Host "   Installez PHP ou ajoutez-le au PATH système." -ForegroundColor Yellow
    exit 1
}

# Vérifier le fichier .env
if (-not (Test-Path ".env")) {
    Write-Host "⚠️  Attention: Fichier .env non trouvé!" -ForegroundColor Yellow
    Write-Host "   Créez un fichier .env avec vos paramètres de base de données." -ForegroundColor Yellow
    Write-Host ""
    $continue = Read-Host "Continuer quand même? (o/N)"
    if ($continue -ne "o" -and $continue -ne "O") {
        exit 1
    }
}

Write-Host ""
Write-Host "Exécution du script d'analyse..." -ForegroundColor Yellow
Write-Host ""

# Mesurer le temps d'exécution
$startTime = Get-Date

# Exécuter le déploiement intelligent
php deploy-smart.php

$endTime = Get-Date
$duration = $endTime - $startTime

# Vérifier le code de sortie
if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host "✅ Déploiement terminé avec succès!" -ForegroundColor Green
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host ""
    Write-Host "⏱️  Temps d'exécution: $($duration.TotalSeconds.ToString('F2')) secondes" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "📋 Prochaines étapes recommandées:" -ForegroundColor Yellow
    Write-Host "   1. Testez la connexion: php artisan tinker" -ForegroundColor White
    Write-Host "   2. Vérifiez les logs: storage/logs/laravel.log" -ForegroundColor White
    Write-Host "   3. Testez l'authentification sur le site" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Red
    Write-Host "⚠️  Le script s'est terminé avec des erreurs" -ForegroundColor Red
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Red
    Write-Host ""
    Write-Host "🔍 Conseils de dépannage:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  1. Vérifier la connexion MySQL:" -ForegroundColor White
    Write-Host "     • Ouvrez le fichier .env" -ForegroundColor Gray
    Write-Host "     • Vérifiez DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  2. Vérifier MySQL:" -ForegroundColor White
    Write-Host "     • Service MySQL est-il démarré?" -ForegroundColor Gray
    Write-Host "     • Pouvez-vous vous connecter avec les identifiants?" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  3. Consulter les logs:" -ForegroundColor White
    Write-Host "     • storage/logs/laravel.log" -ForegroundColor Gray
    Write-Host ""
    Write-Host "  4. Tester la connexion:" -ForegroundColor White
    Write-Host "     php artisan tinker --execute=`"DB::connection()->getPdo();`"" -ForegroundColor Gray
    Write-Host ""
    exit $LASTEXITCODE
}
