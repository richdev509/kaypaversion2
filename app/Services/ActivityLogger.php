<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Enregistrer une activité
     */
    public static function log(
        string $actionType,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $changes = null,
        ?string $reason = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'reason' => $reason,
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Logger une création
     */
    public static function logCreate(string $modelType, int $modelId, string $description, ?string $reason = null): ActivityLog
    {
        return self::log('create', $description, $modelType, $modelId, null, $reason);
    }

    /**
     * Logger une modification
     */
    public static function logUpdate(string $modelType, int $modelId, string $description, array $changes, ?string $reason = null): ActivityLog
    {
        return self::log('update', $description, $modelType, $modelId, $changes, $reason);
    }

    /**
     * Logger une suppression
     */
    public static function logDelete(string $modelType, int $modelId, string $description, ?string $reason = null): ActivityLog
    {
        return self::log('delete', $description, $modelType, $modelId, null, $reason);
    }

    /**
     * Logger une connexion
     */
    public static function logLogin(string $description): ActivityLog
    {
        return self::log('login', $description);
    }

    /**
     * Logger une déconnexion
     */
    public static function logLogout(string $description): ActivityLog
    {
        return self::log('logout', $description);
    }

    /**
     * Logger un accès
     */
    public static function logAccess(string $description, ?string $modelType = null, ?int $modelId = null): ActivityLog
    {
        return self::log('access', $description, $modelType, $modelId);
    }

    /**
     * Logger une action personnalisée
     */
    public static function logCustom(string $actionType, string $description, ?string $reason = null): ActivityLog
    {
        return self::log($actionType, $description, null, null, null, $reason);
    }
}
