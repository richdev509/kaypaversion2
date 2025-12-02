<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\AffiliateVerificationCode;
use App\Mail\AffiliateRequestReceived;

class AffiliatePublicController extends Controller
{
    /**
     * Afficher le formulaire de demande de partenariat
     */
    public function showForm()
    {
        return view('affiliate.public-form');
    }

    /**
     * Soumettre la demande de partenariat
     */
    public function submitRequest(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'required|string|unique:affiliates,telephone',
            'email' => 'required|email|unique:affiliates,email',
            'whatsapp' => 'nullable|string',
        ], [
            'telephone.unique' => 'Ce numéro de téléphone est déjà enregistré',
            'email.unique' => 'Cet email est déjà enregistré',
        ]);

        // Générer code de vérification
        $codeVerification = Affiliate::generateVerificationCode();

        // Créer la demande
        $affiliate = Affiliate::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp ?? $request->telephone,
            'code_verification' => $codeVerification,
            'status' => 'en_attente',
        ]);

        // Envoyer l'email avec le code
        try {
            Mail::to($affiliate->email)->send(new AffiliateVerificationCode($affiliate, $codeVerification));
        } catch (\Exception $e) {
            // Continuer même si l'email échoue
        }

        return redirect()->route('affiliate.verify-form', $affiliate->id)
            ->with('success', 'Demande soumise ! Vérifiez votre email pour le code de vérification.');
    }

    /**
     * Afficher le formulaire de vérification
     */
    public function showVerifyForm($id)
    {
        $affiliate = Affiliate::findOrFail($id);

        if ($affiliate->email_verifie) {
            return redirect()->route('home')->with('info', 'Email déjà vérifié');
        }

        return view('affiliate.verify-code', compact('affiliate'));
    }

    /**
     * Vérifier le code
     */
    public function verifyCode(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $affiliate = Affiliate::findOrFail($id);

        if ($affiliate->email_verifie) {
            return back()->with('error', 'Email déjà vérifié');
        }

        if ($request->code !== $affiliate->code_verification) {
            return back()->with('error', 'Code de vérification incorrect');
        }

        // Marquer comme vérifié
        $affiliate->update([
            'email_verifie' => true,
            'email_verifie_at' => now(),
        ]);

        // Envoyer email de confirmation
        try {
            Mail::to($affiliate->email)->send(new AffiliateRequestReceived($affiliate));
        } catch (\Exception $e) {
            // Continuer
        }

        return view('affiliate.verification-success', compact('affiliate'));
    }

    /**
     * Renvoyer le code de vérification
     */
    public function resendCode($id)
    {
        $affiliate = Affiliate::findOrFail($id);

        if ($affiliate->email_verifie) {
            return back()->with('error', 'Email déjà vérifié');
        }

        // Générer nouveau code
        $newCode = Affiliate::generateVerificationCode();
        $affiliate->update(['code_verification' => $newCode]);

        // Renvoyer l'email
        try {
            Mail::to($affiliate->email)->send(new AffiliateVerificationCode($affiliate, $newCode));
            return back()->with('success', 'Code de vérification renvoyé à votre email');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi de l\'email');
        }
    }
}
