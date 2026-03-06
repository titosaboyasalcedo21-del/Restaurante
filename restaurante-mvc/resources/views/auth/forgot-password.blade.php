<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña — RestaurantChain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0a0a0f 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: rgba(255,255,255,.05);
            backdrop-filter: blur(10px);
            border: 1px solid #1e1e2e;
            border-radius: 1.5rem;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,.5);
        }
        .brand-logo {
            color: #e94560;
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: .5rem;
            background: linear-gradient(90deg, #ff4d4d, #ff6b35);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-sub {
            color: rgba(255,255,255,.5);
            text-align: center;
            font-size: .85rem;
            margin-bottom: 2rem;
        }
        .form-control {
            background: #0f0f1a;
            border: 1px solid #2a2a3a;
            color: #ccc;
            border-radius: .75rem;
            padding: .75rem 1rem;
        }
        .form-control:focus {
            background: #0f0f1a;
            border-color: #e94560;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(233,69,96,.25);
        }
        .form-control::placeholder { color: rgba(255,255,255,.35); }
        .form-label { color: rgba(255,255,255,.75); font-size: .875rem; }
        .btn-reset {
            background: linear-gradient(90deg, #ff4d4d, #ff6b35);
            border: none;
            padding: .75rem;
            font-weight: 600;
            border-radius: .75rem;
            width: 100%;
            color: white;
        }
        .btn-reset:hover {
            background: linear-gradient(90deg, #ff6b35, #ff4d4d);
            color: white;
        }
        .back-link {
            color: #555;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link:hover {
            color: #ff6b6b;
            text-decoration: underline;
        }
        .alert-success {
            background: rgba(0,230,118,.15);
            border: 1px solid rgba(0,230,118,.3);
            color: #00e676;
            border-radius: .75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .alert-error {
            background: rgba(220,53,69,.15);
            border: 1px solid rgba(220,53,69,.3);
            color: #ff6b6b;
            border-radius: .75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<div class="reset-card">
    <div class="brand-logo">
        <i class="bi bi-shop-window me-2"></i>RestaurantChain
    </div>
    <p class="brand-sub">Sistema de Gestión de Restaurantes</p>

    <h4 class="text-white text-center mb-4">Recuperar contraseña</h4>
    <p class="text-center" style="color: #555; margin-bottom: 1.5rem;">
        Te enviaremos un enlace a tu correo registrado
    </p>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert-success">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        </div>
    @endif

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert-error">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" name="email" id="email"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="tu@correo.com" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-reset">
            <i class="bi bi-envelope me-2"></i>Enviar Enlace de Recuperación
        </button>
    </form>

    <a href="{{ route('login') }}" class="back-link">
        <i class="bi bi-arrow-left me-1"></i>Volver al login
    </a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
