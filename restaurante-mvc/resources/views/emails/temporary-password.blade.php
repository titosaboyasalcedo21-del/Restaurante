<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu acceso fue restablecido — RestaurantChain</title>
    <style>
        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0f0f1a;
            margin: 0;
            padding: 0;
            color: #ccc;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background: #0f0f1a;
            border: 1px solid #2a2a3a;
            border-radius: 16px;
            padding: 40px;
        }
        .logo {
            color: #ff4d4d;
            font-size: 28px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 8px;
            background: linear-gradient(90deg, #ff4d4d, #ff6b35);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle {
            color: #555;
            text-align: center;
            font-size: 14px;
            margin-bottom: 30px;
        }
        h1 {
            color: #fff;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        p {
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .highlight {
            background: rgba(255, 77, 77, 0.15);
            border: 1px solid rgba(255, 77, 77, 0.3);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            margin: 20px 0;
        }
        .password-code {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: 700;
            color: #ff4d4d;
            letter-spacing: 2px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(90deg, #ff4d4d, #ff6b35);
            color: #fff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .btn-container {
            text-align: center;
        }
        .warning {
            background: rgba(255, 152, 0, 0.15);
            border: 1px solid rgba(255, 152, 0, 0.3);
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #555;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <i class="bi bi-shop-window"></i> RestaurantChain
            </div>
            <p class="subtitle">Sistema de Gestión de Restaurantes</p>

            <h1>Tu acceso fue restablecido</h1>

            <p>Hola <strong>{{ $user->name }}</strong>,</p>

            <p>El administrador generó una nueva contraseña temporal para tu cuenta:</p>

            <div class="highlight">
                <p style="margin: 0; color: #888; font-size: 14px;">Tu contraseña temporal:</p>
                <div class="password-code">{{ $temporaryPassword }}</div>
            </div>

            <div class="btn-container">
                <a href="{{ url('/login') }}" class="btn">Iniciar Sesión</a>
            </div>

            <div class="warning">
                <strong><i class="bi bi-exclamation-triangle"></i> Por seguridad:</strong>
                <br>• Esta contraseña es temporal y debe ser cambiada en tu próximo inicio de sesión.
                <br>• Te recomendamos usar una contraseña que contenga al menos 8 caracteres con letras mayúsculas, minúsculas y números.
            </div>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                Si no solicitaste este cambio, por favor contacta al administrador inmediatamente.
            </p>

            <div class="footer">
                <p>© {{ date('Y') }} RestaurantChain. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
