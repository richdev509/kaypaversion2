<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer ou mettre à jour utilisateur admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@kaypa.com'],
            [
                'name' => 'Admin KAYPA',
                'password' => Hash::make('password123'),
                'branch_id' => 1, // Saga Center
                'telephone' => '509-0000-0000',
                'role' => 'admin',
            ]
        );

        // Assigner rôle Spatie
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        echo "\n✅ Admin créé/mis à jour: admin@kaypa.com / password123 (Branch: Saga Center)\n";
    }
}
