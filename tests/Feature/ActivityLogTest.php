<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityLogTest extends TestCase
{
    public function test_activity_logger_service_works()
    {
        // Trouver un utilisateur admin
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->markTestSkipped('Aucun utilisateur admin trouvé dans la base de données');
        }

        // Se connecter en tant qu'admin
        $this->actingAs($admin);

        // Tester la création d'un log
        $log = ActivityLogger::logCustom('test', 'Test du système de monitoring');

        $this->assertNotNull($log);
        $this->assertEquals('test', $log->action_type);
        $this->assertEquals('Test du système de monitoring', $log->description);
        $this->assertEquals($admin->id, $log->user_id);

        echo "\n✅ Service ActivityLogger fonctionne correctement\n";
        echo "Log créé avec l'ID: {$log->id}\n";
    }

    public function test_activity_logs_route_requires_admin()
    {
        // Créer un utilisateur non-admin
        $agent = User::where('role', 'agent')->first();

        if (!$agent) {
            $this->markTestSkipped('Aucun agent trouvé dans la base de données');
        }

        // Essayer d'accéder à la page de monitoring en tant qu'agent
        $response = $this->actingAs($agent)->get(route('activity-logs.index'));

        // Devrait recevoir une erreur 403
        $response->assertStatus(403);

        echo "\n✅ Protection admin fonctionne - Agent bloqué\n";
    }

    public function test_admin_can_access_monitoring()
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->markTestSkipped('Aucun utilisateur admin trouvé dans la base de données');
        }

        // Se connecter en tant qu'admin
        $response = $this->actingAs($admin)->get(route('activity-logs.index'));

        // Devrait fonctionner (200)
        $response->assertStatus(200);

        echo "\n✅ Admin peut accéder au monitoring\n";
    }
}
