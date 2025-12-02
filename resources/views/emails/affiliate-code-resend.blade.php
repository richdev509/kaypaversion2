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
        .code-box { background: #f8f9fa; border: 3px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px; background: white; padding: 15px; border-radius: 4px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #777; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Votre Code de Parrainage</h1>
            <p>Programme Partenaire Kaypa</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $affiliate->nom_complet }}</strong>,</p>

            <p>Suite à votre demande, voici à nouveau votre code de parrainage :</p>

            <div class="code-box">
                <h3 style="margin-top: 0; color: #667eea;">Votre Code Unique</h3>
                <div class="code">{{ $affiliate->code_parrain }}</div>
            </div>

            <h3>📝 Rappel</h3>
            <ul>
                <li>Partagez ce code avec vos contacts</li>
                <li>Gagnez <strong>25 GDS</strong> par nouveau client</li>
                <li>Le client doit saisir ce code lors de son inscription</li>
            </ul>

            <p>
                <strong>Email :</strong> {{ $affiliate->email }}<br>
                <strong>Téléphone :</strong> {{ $affiliate->telephone }}
            </p>

            <p>
                Cordialement,<br>
                <strong>L'équipe Kaypa</strong>
            </p>
        </div>

        <div class="footer">
            <p><strong>Kaypa - Programme de Partenariat</strong></p>
            <p>Email: contact@mykaypa.com</p>
        </div>
    </div>
</body>
</html>
