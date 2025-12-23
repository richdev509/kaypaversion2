<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $query = ActivityLog::with('user')->latest();

        // Filtre par type d'action
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // Filtre par utilisateur
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtre par type de modèle
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filtre par recherche (description)
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Filtre par période
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Récupérer les utilisateurs pour le filtre
        $users = User::orderBy('name')->get();

        // Types d'actions disponibles
        $actionTypes = ActivityLog::distinct()->pluck('action_type')->sort()->values();

        // Types de modèles disponibles
        $modelTypes = ActivityLog::whereNotNull('model_type')->distinct()->pluck('model_type')->sort()->values();

        return view('activity-logs.index', compact('logs', 'users', 'actionTypes', 'modelTypes'));
    }

    public function show(ActivityLog $log)
    {
        // Vérifier que l'utilisateur est admin
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        $log->load('user');

        return view('activity-logs.show', compact('log'));
    }
}
