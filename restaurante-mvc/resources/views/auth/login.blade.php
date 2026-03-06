<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — RestaurantChain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255,255,255,.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.1);
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
        }
        .brand-sub {
            color: rgba(255,255,255,.5);
            text-align: center;
            font-size: .85rem;
            margin-bottom: 2rem;
        }
        .form-control, .form-select {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            color: #fff;
            border-radius: .75rem;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,.12);
            border-color: #e94560;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(233,69,96,.25);
        }
        .form-control::placeholder { color: rgba(255,255,255,.35); }
        .form-label { color: rgba(255,255,255,.75); font-size: .875rem; }
        .btn-login {
            background: linear-gradient(135deg, #e94560, #c0392b);
            border: none;
            border-radius: .75rem;
            padding: .75rem;
            font-weight: 600;
            letter-spacing: .5px;
            transition: all .3s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(233,69,96,.4); }
        .demo-box {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: .75rem;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        .demo-box small { color: rgba(255,255,255,.5); }
        .demo-item { color: rgba(255,255,255,.8); font-size: .8rem; }
        .forgot-link {
            color: #ff6b6b;
            font-size: 12px;
            text-decoration: none;
        }
        .forgot-link:hover {
            color: #ff8a8a;
            text-decoration: underline;
        }
        .invalid-feedback { color: #ff6b6b; }
        .alert-danger { background: rgba(220,53,69,.2); border-color: rgba(220,53,69,.3); color: #ff6b6b; border-radius: .75rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-logo">
        <i class="bi bi-shop-window me-2"></i>RestaurantChain
    </div>
    <div class="brand-sub">Sistema de Gestión de Productos</div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success mb-3" style="background: rgba(0,230,118,.15); border: 1px solid rgba(0,230,118,.3); color: #00e676; border-radius: .75rem; padding: 1rem;">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger mb-3">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.5)">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email" name="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                       placeholder="admin@restaurantchain.com" value="{{ old('email') }}" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0" style="border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.5)">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" class="form-control border-start-0 @error('password') is-invalid @enderror"
                       placeholder="••••••••" required>
            </div>
        </div>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember" style="color:rgba(255,255,255,.6);font-size:.85rem">
                    Recordarme
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-login btn-primary w-100 text-white">
            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
        </button>

        <div class="text-center mt-3">
            <a href="{{ route('password.request') }}" class="forgot-link">
                ¿Olvidaste tu contraseña?
            </a>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
