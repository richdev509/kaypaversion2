<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "👤 Création nouvel admin...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$user = User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@kaypa.ht',
    'password' => Hash::make('SuperAdmin@2024!'),
    'telephone' => '+509 0000-0001',
    'branch_id' => 1,
]);

$user->assignRole('admin');

echo "✅ Admin créé avec succès!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📧 Email: superadmin@kaypa.ht\n";
echo "🔑 Mot de passe: SuperAdmin@2024!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
