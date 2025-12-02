<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos identifiants Kaypa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credential-item {
            margin: 10px 0;
        }
        .credential-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        .credential-value {
            font-size: 18px;
            color: #667eea;
            font-weight: bold;
            background: white;
            padding: 10px;
            border-radius: 4px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .login-button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 12px;
        }
        .logo {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">KAYPA</div>
            <h1>Bienvenue sur Kaypa</h1>
            <p>Vos identifiants d'accès</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $client->full_name }}</strong>,</p>

            <p>Votre compte Kaypa a été activé avec succès. Vous pouvez maintenant accéder à votre espace client en ligne pour consulter vos comptes et effectuer des opérations.</p>

            <div class="credentials-box">
                <h3 style="margin-top: 0; color: #667eea;">🔐 Vos Identifiants de Connexion</h3>

                <div class="credential-item">
                    <span class="credential-label">Identifiant Client (Login):</span>
                    <span class="credential-value">{{ $client->client_id }}</span>
                </div>

                <div class="credential-item">
                    <span class="credential-label">Mot de passe temporaire:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>

                <div class="credential-item">
                    <span class="credential-label">URL de connexion:</span>
                    <span class="credential-value" style="font-size: 14px;">https://mykaypa.com/mobile/login</span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="https://mykaypa.com/mobile/login" class="login-button">
                    Se Connecter Maintenant
                </a>
            </div>

            <div class="warning-box">
                <strong>⚠️ Important:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Ce mot de passe est <strong>temporaire</strong></li>
                    <li>Il est recommandé de le changer lors de votre première connexion</li>
                    <li>Ne partagez jamais vos identifiants avec qui que ce soit</li>
                    <li>Notre équipe ne vous demandera jamais votre mot de passe</li>
                </ul>
            </div>

            <h3>📱 Comment se connecter ?</h3>
            <ol>
                <li>Visitez <strong>https://mykaypa.com/mobile/login</strong></li>
                <li>Entrez votre <strong>Client ID: {{ $client->client_id }}</strong></li>
                <li>Entrez le mot de passe reçu dans cet email</li>
                <li>Cliquez sur "Se connecter"</li>
            </ol>

            <p style="margin-top: 30px;">Si vous avez des questions ou besoin d'assistance, n'hésitez pas à nous contacter.</p>

            <p style="margin-top: 20px;">
                Cordialement,<br>
                <strong>L'équipe Kaypa Support</strong>
            </p>
        </div>

        <div class="footer">
            <p><strong>Kaypa - Système de Gestion d'Épargne</strong></p>
            <p>Email: contact@mykaypa.com</p>
            <p style="margin-top: 10px; color: #999;">
                Cet email a été envoyé automatiquement. Veuillez ne pas répondre directement à ce message.
            </p>
        </div>
    </div>
</body>
</html>
