<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ClientAccessGranted;

class ClientAccessController extends Controller
{
    /**
     * Afficher le formulaire de gestion d'accès
     */
    public function index()
    {
        return view('client-access.index');
    }

    /**
     * Rechercher un client par ID
     */
    public function search(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string'
        ]);

        // Rechercher par client_id ou par ID numérique
        $client = Client::where('client_id', $request->client_id)
            ->orWhere('id', $request->client_id)
            ->first();

        if (!$client) {
            return back()->with('error', 'Client non trouvé');
        }

        return view('client-access.show', compact('client'));
    }

    /**
     * Accorder l'accès au client
     */
    public function grantAccess(Request $request, Client $client)
    {
        if (empty($client->email)) {
            return back()->with('error', 'Le client n\'a pas d\'adresse email enregistrée');
        }

        // Générer un mot de passe aléatoire
        $password = $this->generatePassword();

        // Mettre à jour le client
        $client->password = Hash::make($password);
        $client->password_reset = true; // Indiquer que c'est un mot de passe temporaire
        $client->save();

        // Envoyer l'email
        try {
            Mail::to($client->email)->send(new ClientAccessGranted($client, $password));

            return back()->with('success', 'Accès accordé avec succès. Un email a été envoyé au client avec ses identifiants.');
        } catch (\Exception $e) {
            return back()->with('error', 'Accès accordé mais l\'envoi de l\'email a échoué: ' . $e->getMessage());
        }
    }

    /**
     * Révoquer l'accès du client
     */
    public function revokeAccess(Client $client)
    {
        $client->password = null;
        $client->password_reset = false;
        $client->save();

        return back()->with('success', 'Accès révoqué avec succès');
    }

    /**
     * Générer un mot de passe aléatoire sécurisé
     */
    private function generatePassword(): string
    {
        // Génère un mot de passe de 10 caractères avec majuscules, minuscules et chiffres
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '@#$';

        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];

        // Mélanger les caractères
        return str_shuffle($password);
    }
}
