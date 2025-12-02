<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Parrainage;
use App\Models\AffiliatePaiement;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\AffiliateApproved;
use App\Mail\AffiliateCodeResend;

class AffiliateController extends Controller
{
    /**
     * Liste des demandes de partenariat
     */
    public function index(Request $request)
    {
        $query = Affiliate::query()->with(['approuvePar']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('code_parrain', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $affiliates = $query->latest()->paginate(20);

        // Statistiques
        $stats = [
            'total' => Affiliate::count(),
            'en_attente' => Affiliate::where('status', 'en_attente')->count(),
            'approuve' => Affiliate::where('status', 'approuve')->count(),
            'total_parrainages' => Parrainage::count(),
            'total_bonus' => Parrainage::sum('bonus_gagne'),
        ];

        return view('affiliates.index', compact('affiliates', 'stats'));
    }

    /**
     * Détails d'un affilié
     */
    public function show(Affiliate $affiliate)
    {
        $affiliate->load([
            'parrainages.client',
            'parrainages.account',
            'paiements.effectuePar'
        ]);

        $stats = [
            'total_parrainages' => $affiliate->parrainages()->count(),
            'parrainages_valides' => $affiliate->parrainages()->valide()->count(),
            'parrainages_payes' => $affiliate->parrainages()->paye()->count(),
            'total_gagne' => $affiliate->parrainages()->sum('bonus_gagne'),
            'total_paye' => $affiliate->paiements()->sum('montant'),
        ];

        return view('affiliates.show', compact('affiliate', 'stats'));
    }

    /**
     * Approuver une demande
     */
    public function approve(Affiliate $affiliate)
    {
        if ($affiliate->status !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée');
        }

        if (!$affiliate->email_verifie) {
            return back()->with('error', 'L\'email n\'a pas été vérifié');
        }

        DB::beginTransaction();
        try {
            // Générer code de parrainage
            $codeParrain = Affiliate::generateCodeParrain();

            $affiliate->update([
                'status' => 'approuve',
                'code_parrain' => $codeParrain,
                'approuve_at' => now(),
                'approuve_by' => Auth::id(),
            ]);

            // Envoyer email avec code de parrainage
            Mail::to($affiliate->email)->send(new AffiliateApproved($affiliate));

            DB::commit();

            return back()->with('success', 'Affilié approuvé avec succès ! Code de parrainage envoyé par email.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une demande
     */
    public function reject(Request $request, Affiliate $affiliate)
    {
        $request->validate([
            'motif_rejet' => 'required|string|max:500',
        ]);

        if ($affiliate->status !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée');
        }

        $affiliate->update([
            'status' => 'rejete',
            'motif_rejet' => $request->motif_rejet,
        ]);

        return back()->with('success', 'Demande rejetée');
    }

    /**
     * Bloquer/Débloquer un affilié
     */
    public function toggleBlock(Affiliate $affiliate)
    {
        if ($affiliate->status === 'bloque') {
            $affiliate->update(['status' => 'approuve']);
            return back()->with('success', 'Affilié débloqué');
        } else {
            $affiliate->update(['status' => 'bloque']);
            return back()->with('success', 'Affilié bloqué');
        }
    }

    /**
     * Renvoyer le code de parrainage
     */
    public function resendCode(Affiliate $affiliate)
    {
        if ($affiliate->status !== 'approuve' || !$affiliate->code_parrain) {
            return back()->with('error', 'Impossible de renvoyer le code');
        }

        try {
            Mail::to($affiliate->email)->send(new AffiliateCodeResend($affiliate));
            return back()->with('success', 'Code de parrainage renvoyé par email');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi de l\'email');
        }
    }

    /**
     * Effectuer un paiement
     */
    public function storePaiement(Request $request, Affiliate $affiliate)
    {
        $request->validate([
            'montant' => 'required|numeric|min:1',
            'methode' => 'required|in:cash,moncash,bank_transfer,compte_kaypa',
            'note' => 'nullable|string|max:500',
        ]);

        if ($request->montant > $affiliate->solde_bonus) {
            return back()->with('error', 'Montant supérieur au solde disponible');
        }

        DB::beginTransaction();
        try {
            // Créer le paiement
            AffiliatePaiement::create([
                'affiliate_id' => $affiliate->id,
                'montant' => $request->montant,
                'methode' => $request->methode,
                'note' => $request->note,
                'effectue_by' => Auth::id(),
            ]);

            // Déduire du solde
            $affiliate->decrement('solde_bonus', $request->montant);

            DB::commit();

            return back()->with('success', 'Paiement effectué avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }
}
