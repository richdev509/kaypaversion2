<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignRolesToExistingUsersSeeder extends Seeder
{
    /**
     * Assigner des rôles aux utilisateurs existants basé sur leur colonne 'role'
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Si l'utilisateur a déjà un rôle Spatie, on le garde
            if ($user->roles->isNotEmpty()) {
                $this->command->info("User {$user->email} already has role: {$user->getRoleNames()->first()}");
                continue;
            }

            // Sinon, assigner le rôle basé sur la colonne 'role' de la table users
            if (!empty($user->role)) {
                try {
                    $user->assignRole($user->role);
                    $this->command->info("Assigned role '{$user->role}' to {$user->email}");
                } catch (\Exception $e) {
                    // Si le rôle n'existe pas, assigner 'agent' par défaut
                    $user->assignRole('agent');
                    $this->command->warn("Role '{$user->role}' not found for {$user->email}. Assigned 'agent' instead.");
                }
            } else {
                // Pas de rôle défini, assigner 'agent' par défaut
                $user->assignRole('agent');
                $this->command->info("No role found for {$user->email}. Assigned 'agent' by default.");
            }
        }

        $this->command->info("\nRoles assignment completed!");
    }
}
