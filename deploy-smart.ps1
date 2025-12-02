# Script de déploiement intelligent KAYPA
# Analyse et répare automatiquement la base de données

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
    exit 1
}

# Exécuter le déploiement intelligent
Write-Host "Exécution du script d'analyse..." -ForegroundColor Yellow
Write-Host ""

php deploy-smart.php

# Vérifier le code de sortie
if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host "✅ Script terminé avec succès!" -ForegroundColor Green
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Red
    Write-Host "⚠️  Le script s'est terminé avec des erreurs" -ForegroundColor Red
    Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Red
    Write-Host ""
    Write-Host "Conseils de dépannage:" -ForegroundColor Yellow
    Write-Host "  1. Vérifiez la connexion à la base de données dans .env" -ForegroundColor White
    Write-Host "  2. Assurez-vous que MySQL est démarré" -ForegroundColor White
    Write-Host "  3. Vérifiez les logs dans storage/logs/" -ForegroundColor White
    Write-Host ""
    exit $LASTEXITCODE
}
