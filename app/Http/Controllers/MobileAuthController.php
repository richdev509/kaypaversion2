<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class MobileAuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion mobile
     */
    public function showLoginForm()
    {
        return view('mobile.login');
    }

    /**
     * Traiter la connexion mobile
     */
    public function login(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'password' => 'required|string'
        ]);

        // Rechercher le client par client_id
        $client = Client::where('client_id', $request->client_id)->first();

        if (!$client) {
            return back()->withErrors([
                'client_id' => 'Identifiant client invalide.'
            ])->onlyInput('client_id');
        }

        // Vérifier si le client a un mot de passe
        if (!$client->password) {
            return back()->withErrors([
                'client_id' => 'Votre compte n\'est pas encore activé. Contactez votre agence.'
            ])->onlyInput('client_id');
        }

        // Vérifier le mot de passe
        if (!Hash::check($request->password, $client->password)) {
            return back()->withErrors([
                'password' => 'Mot de passe incorrect.'
            ])->onlyInput('client_id');
        }

        // Créer une session pour le client
        session([
            'mobile_client_id' => $client->id,
            'mobile_client_name' => $client->full_name,
            'mobile_client_login' => $client->client_id
        ]);

        // Rediriger vers le dashboard mobile
        return redirect()->route('mobile.dashboard');
    }

    /**
     * Afficher le dashboard mobile du client
     */
    public function dashboard()
    {
        if (!session()->has('mobile_client_id')) {
            return redirect()->route('mobile.login');
        }

        $client = Client::with(['accounts.plan', 'branch'])->find(session('mobile_client_id'));

        if (!$client) {
            session()->forget(['mobile_client_id', 'mobile_client_name', 'mobile_client_login']);
            return redirect()->route('mobile.login')->withErrors(['error' => 'Session expirée']);
        }

        return view('mobile.dashboard', compact('client'));
    }

    /**
     * Déconnexion mobile
     */
    public function logout()
    {
        session()->forget(['mobile_client_id', 'mobile_client_name', 'mobile_client_login']);
        return redirect()->route('mobile.login')->with('success', 'Déconnexion réussie');
    }

    /**
     * Changer le mot de passe
     */
    public function showChangePasswordForm()
    {
        if (!session()->has('mobile_client_id')) {
            return redirect()->route('mobile.login');
        }

        return view('mobile.change-password');
    }

    /**
     * Traiter le changement de mot de passe
     */
    public function changePassword(Request $request)
    {
        if (!session()->has('mobile_client_id')) {
            return redirect()->route('mobile.login');
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $client = Client::find(session('mobile_client_id'));

        if (!Hash::check($request->current_password, $client->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect']);
        }

        $client->password = Hash::make($request->new_password);
        $client->password_reset = false; // Désactiver le flag temporaire
        $client->save();

        return redirect()->route('mobile.dashboard')->with('success', 'Mot de passe changé avec succès');
    }
}
