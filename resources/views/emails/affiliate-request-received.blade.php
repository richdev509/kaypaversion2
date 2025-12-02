<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-center; }
        .content { padding: 30px; }
        .info-box { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .footer { background: #f8f9fa; padding: 20px; text-center; color: #777; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Demande Reçue</h1>
            <p>Programme de Partenariat Kaypa</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $affiliate->nom_complet }}</strong>,</p>

            <p>Votre demande de partenariat a été soumise avec succès !</p>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #2e7d32;">📋 Prochaines Étapes</h3>
                <ul style="color: #2e7d32;">
                    <li>Notre équipe va examiner votre demande</li>
                    <li>Vous recevrez une réponse dans les <strong>24 heures</strong></li>
                    <li>Nous vous contacterons par email et WhatsApp</li>
                </ul>
            </div>

            <h3>💰 Programme de Parrainage</h3>
            <p>Une fois approuvé, vous recevrez votre <strong>code de parrainage unique</strong>.</p>
            <p><strong>Gagnez 25 GDS</strong> pour chaque nouveau client qui s'inscrit avec votre code !</p>

            <h3>📞 Contact</h3>
            <p>
                <strong>Email :</strong> {{ $affiliate->email }}<br>
                <strong>Téléphone :</strong> {{ $affiliate->telephone }}<br>
                <strong>WhatsApp :</strong> {{ $affiliate->whatsapp ?? $affiliate->telephone }}
            </p>

            <p>Merci de votre intérêt pour Kaypa !</p>

            <p>
                Cordialement,<br>
                <strong>L'équipe Kaypa</strong>
            </p>
        </div>

        <div class="footer">
            <p><strong>Kaypa - Système de Gestion d'Épargne</strong></p>
            <p>Email: contact@mykaypa.com</p>
        </div>
    </div>
</body>
</html>
