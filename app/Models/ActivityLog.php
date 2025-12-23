<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action_type',
        'model_type',
        'model_id',
        'description',
        'reason',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le modèle concerné de manière dynamique
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->morphTo();
        }
        return null;
    }
}
