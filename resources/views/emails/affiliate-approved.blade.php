<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .code-box { background: #f8f9fa; border: 3px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .code { font-size: 32px; font-weight: bold; color: #10b981; letter-spacing: 5px; background: white; padding: 15px; border-radius: 4px; }
        .success-box { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #777; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 60px;">🎉</div>
            <h1>Félicitations !</h1>
            <p>Vous êtes maintenant Partenaire Kaypa</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $affiliate->nom_complet }}</strong>,</p>

            <p>Nous avons le plaisir de vous informer que votre demande de partenariat a été <strong>APPROUVÉE</strong> !</p>

            <div class="code-box">
                <h3 style="margin-top: 0; color: #10b981;">🎫 Votre Code de Parrainage Unique</h3>
                <div class="code">{{ $affiliate->code_parrain }}</div>
                <p style="margin-bottom: 0; font-size: 12px; color: #666;">Partagez ce code avec vos contacts</p>
            </div>

            <div class="success-box">
                <h3 style="margin-top: 0; color: #2e7d32;">💰 Comment Gagner 25 GDS</h3>
                <ol style="color: #2e7d32;">
                    <li>Partagez votre code <strong>{{ $affiliate->code_parrain }}</strong> avec vos contacts</li>
                    <li>Le nouveau client saisit votre code lors de son inscription</li>
                    <li>Dès qu'il ouvre son premier compte, vous recevez <strong>25 GDS</strong> automatiquement !</li>
                </ol>
            </div>

            <h3>📊 Suivi de vos Parrainages</h3>
            <p>
                <strong>Email :</strong> {{ $affiliate->email }}<br>
                <strong>Code :</strong> {{ $affiliate->code_parrain }}<br>
                <strong>Bonus par client :</strong> 25 GDS
            </p>

            <h3>⚠️ Conseils Importants</h3>
            <ul>
                <li>Conservez votre code en sécurité</li>
                <li>Ne le partagez qu'avec des personnes de confiance</li>
                <li>Vous pouvez demander à nos agents le solde de vos commissions</li>
                <li>Les paiements se font après validation des comptes</li>
            </ul>

            <p style="margin-top: 30px;">
                Bienvenue dans la famille des Partenaires Kaypa !<br>
                <strong>L'équipe Kaypa</strong>
            </p>
        </div>

        <div class="footer">
            <p><strong>Kaypa - Programme de Partenariat</strong></p>
            <p>Email: contact@mykaypa.com</p>
            <p style="margin-top: 10px;">Code oublié ? Contactez-nous pour le recevoir à nouveau.</p>
        </div>
    </div>
</body>
</html>
