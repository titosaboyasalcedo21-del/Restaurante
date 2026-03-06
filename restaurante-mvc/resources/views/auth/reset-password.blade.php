<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña — RestaurantChain</title>
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
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.75rem;
        }
        .requirement {
            color: #555;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .requirement.valid {
            color: #00e676;
        }
        .requirement i {
            font-size: 0.7rem;
        }
        .input-group-text {
            background: transparent;
            border: 1px solid #2a2a3a;
            border-left: none;
            color: rgba(255,255,255,.5);
        }
        .form-control.toggle-password {
            border-right: none;
        }
    </style>
</head>
<body>
<div class="reset-card">
    <div class="brand-logo">
        <i class="bi bi-shop-window me-2"></i>RestaurantChain
    </div>
    <p class="brand-sub">Sistema de Gestión de Restaurantes</p>

    <h4 class="text-white text-center mb-4">Nueva contraseña</h4>

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="alert alert-danger" style="background: rgba(220,53,69,.15); border: 1px solid rgba(220,53,69,.3); color: #ff6b6b; border-radius: .75rem; padding: 1rem; margin-bottom: 1.5rem;">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address (hidden/readonly) -->
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" name="email" id="email"
                   class="form-control"
                   value="{{ old('email', $request->email) }}" readonly
                   style="opacity: 0.7;">
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Nueva contraseña</label>
            <div class="input-group">
                <input type="password" name="password" id="password"
                       class="form-control toggle-password @error('password') is-invalid @enderror"
                       placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                <span class="input-group-text cursor-pointer" onclick="togglePassword('password', 'toggle-icon-1')">
                    <i class="bi bi-eye" id="toggle-icon-1"></i>
                </span>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <!-- Password Requirements -->
            <div class="password-requirements">
                <div class="requirement" id="req-length">
                    <i class="bi bi-circle"></i> Mínimo 8 caracteres
                </div>
                <div class="requirement" id="req-uppercase">
                    <i class="bi bi-circle"></i> Al menos una mayúscula
                </div>
                <div class="requirement" id="req-number">
                    <i class="bi bi-circle"></i> Al menos un número
                </div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <div class="input-group">
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control toggle-password @error('password_confirmation') is-invalid @enderror"
                       placeholder="Repite tu contraseña" required autocomplete="new-password">
                <span class="input-group-text cursor-pointer" onclick="togglePassword('password_confirmation', 'toggle-icon-2')">
                    <i class="bi bi-eye" id="toggle-icon-2"></i>
                </span>
            </div>
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div id="password-match" class="requirement mt-2" style="display: none;">
                <i class="bi bi-circle"></i> Las contraseñas coinciden
            </div>
        </div>

        <button type="submit" class="btn btn-reset">
            <i class="bi bi-key me-2"></i>Restablecer contraseña
        </button>
    </form>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Real-time password validation
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');

    passwordInput.addEventListener('input', function() {
        const password = this.value;

        // Check length
        const reqLength = document.getElementById('req-length');
        if (password.length >= 8) {
            reqLength.classList.add('valid');
            reqLength.querySelector('i').classList.remove('bi-circle');
            reqLength.querySelector('i').classList.add('bi-check-circle-fill');
        } else {
            reqLength.classList.remove('valid');
            reqLength.querySelector('i').classList.remove('bi-check-circle-fill');
            reqLength.querySelector('i').classList.add('bi-circle');
        }

        // Check uppercase
        const reqUppercase = document.getElementById('req-uppercase');
        if (/[A-Z]/.test(password)) {
            reqUppercase.classList.add('valid');
            reqUppercase.querySelector('i').classList.remove('bi-circle');
            reqUppercase.querySelector('i').classList.add('bi-check-circle-fill');
        } else {
            reqUppercase.classList.remove('valid');
            reqUppercase.querySelector('i').classList.remove('bi-check-circle-fill');
            reqUppercase.querySelector('i').classList.add('bi-circle');
        }

        // Check number
        const reqNumber = document.getElementById('req-number');
        if (/[0-9]/.test(password)) {
            reqNumber.classList.add('valid');
            reqNumber.querySelector('i').classList.remove('bi-circle');
            reqNumber.querySelector('i').classList.add('bi-check-circle-fill');
        } else {
            reqNumber.classList.remove('valid');
            reqNumber.querySelector('i').classList.remove('bi-check-circle-fill');
            reqNumber.querySelector('i').classList.add('bi-circle');
        }

        // Check password match if confirm has value
        checkPasswordMatch();
    });

    confirmInput.addEventListener('input', checkPasswordMatch);

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        const matchDiv = document.getElementById('password-match');

        if (confirm.length > 0) {
            matchDiv.style.display = 'flex';
            if (password === confirm && password.length > 0) {
                matchDiv.classList.add('valid');
                matchDiv.querySelector('i').classList.remove('bi-circle');
                matchDiv.querySelector('i').classList.add('bi-check-circle-fill');
            } else {
                matchDiv.classList.remove('valid');
                matchDiv.querySelector('i').classList.remove('bi-check-circle-fill');
                matchDiv.querySelector('i').classList.add('bi-circle');
            }
        } else {
            matchDiv.style.display = 'none';
        }
    }
</script>

<style>
    .cursor-pointer {
        cursor: pointer;
    }
</style>
</body>
</html>
