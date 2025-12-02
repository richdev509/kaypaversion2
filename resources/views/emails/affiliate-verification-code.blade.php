<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .code-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px; text-align: center; }
        .code { font-size: 48px; font-weight: bold; color: #667eea; letter-spacing: 10px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #777; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Code de Vérification</h1>
            <p>Devenir Partenaire Kaypa</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $affiliate->nom_complet }}</strong>,</p>

            <p>Merci de votre intérêt pour notre programme de partenariat ! Pour continuer, veuillez vérifier votre adresse email.</p>

            <div class="code-box">
                <p style="margin: 0; font-size: 14px; color: #666;">Votre code de vérification</p>
                <div class="code">{{ $code }}</div>
            </div>

            <p><strong>⚠️ Important :</strong></p>
            <ul>
                <li>Ce code expire dans <strong>30 minutes</strong></li>
                <li>N'envoyez jamais ce code à qui que ce soit</li>
                <li>L'équipe Kaypa ne vous demandera jamais ce code</li>
            </ul>

            <p>Si vous n'avez pas fait cette demande, ignorez simplement cet email.</p>
        </div>

        <div class="footer">
            <p><strong>Kaypa - Programme de Partenariat</strong></p>
            <p>Email: contact@mykaypa.com</p>
        </div>
    </div>
</body>
</html>
