<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n========================================\n";
echo "Vérification du membre M0001\n";
echo "========================================\n\n";

try {
    $member = DB::table('membres')
        ->where('id_membre', 'M0001')
        ->first();

    if (!$member) {
        echo "❌ Membre M0001 introuvable dans la base de données\n\n";
        exit(1);
    }

    echo "✅ Membre trouvé:\n";
    echo "   ID: {$member->id}\n";
    echo "   ID Membre: {$member->id_membre}\n";
    echo "   Prénom: {$member->first_name}\n";
    echo "   Nom: {$member->last_name}\n";
    echo "   Téléphone: " . ($member->phone ?? 'NULL') . "\n";
    echo "   Email: " . ($member->email ?? 'NULL') . "\n";
    echo "   Actif: " . ($member->is_active ? 'Oui' : 'Non') . "\n";
    echo "   KYC Status: " . ($member->kyc_status ?? 'NULL') . "\n";
    echo "   Branch ID: " . ($member->registered_branch_id ?? 'NULL') . "\n";
    echo "   Créé le: {$member->created_at}\n";
    echo "   Modifié le: {$member->updated_at}\n\n";

    // Vérifications importantes
    echo "Vérifications:\n";

    if (empty($member->phone)) {
        echo "   ⚠️  ATTENTION: Le téléphone est vide ou NULL\n";
        echo "   → La validation échouera car le téléphone est obligatoire\n";
        echo "   → Solution: UPDATE membres SET phone = '46538901' WHERE id_membre = 'M0001';\n";
    } else {
        echo "   ✅ Téléphone présent: {$member->phone}\n";
        echo "   → Format du téléphone: " . preg_replace('/[^\d]/', '', $member->phone) . " (chiffres seulement)\n";
    }

    if (!$member->is_active) {
        echo "   ⚠️  ATTENTION: Le membre est inactif\n";
    } else {
        echo "   ✅ Membre actif\n";
    }

    if ($member->kyc_status !== 'verified') {
        echo "   ⚠️  ATTENTION: KYC non vérifié\n";
    } else {
        echo "   ✅ KYC vérifié\n";
    }

    echo "\n========================================\n";
    echo "Test de recherche via API\n";
    echo "========================================\n\n";

    $apiResult = DB::table('membres')
        ->where('id_membre', 'M0001')
        ->where('is_active', true)
        ->where('kyc_status', 'verified')
        ->first();

    if ($apiResult) {
        echo "✅ Le membre sera trouvé par l'API searchMember\n";
        echo "   → Téléphone retourné: " . ($apiResult->phone ?? 'NULL') . "\n";
    } else {
        echo "❌ Le membre NE sera PAS trouvé par l'API\n";
        echo "   → Vérifier: is_active = true ET kyc_status = 'verified'\n";
    }

    echo "\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . " (ligne " . $e->getLine() . ")\n\n";
    exit(1);
}
