<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - CONTROLA TU VOTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --sirepre-green: #198754;
            --sirepre-dark: #0f2e22;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #edf4ef 0%, #f8fbf9 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .login-card {
            width: min(1020px, 100%);
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 25px 45px rgba(0, 0, 0, .12);
        }

        .brand-panel {
            position: relative;
            min-height: 540px;
            background: linear-gradient(rgba(15, 46, 34, .55), rgba(15, 46, 34, .65)),
                        url("{{ asset('images/login-bg.jpg') }}") center/cover no-repeat;
            color: #fff;
            padding: 34px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-badge {
            display: inline-block;
            padding: .4rem .75rem;
            border-radius: 999px;
            background: rgba(255,255,255,.2);
            font-weight: 600;
            backdrop-filter: blur(3px);
        }

        .brand-title {
            font-weight: 800;
            letter-spacing: .3px;
            line-height: 1.2;
        }

        .form-panel {
            background: #fff;
            padding: 34px;
        }

        .form-control {
            border-radius: 10px;
            padding-top: .72rem;
            padding-bottom: .72rem;
        }

        .btn-login {
            border-radius: 10px;
            padding-top: .72rem;
            padding-bottom: .72rem;
            font-weight: 600;
        }

        @media (max-width: 991.98px) {
            .brand-panel { min-height: 260px; }
        }
    </style>
</head>
<body>
<div class="login-shell">
    <div class="card login-card">
        <div class="row g-0">
            <div class="col-lg-6">
                <div class="brand-panel">
                    <div>
                        <span class="brand-badge">OEP Bolivia 2026</span>
                    </div>
                    <div>
                        <h2 class="brand-title mb-3">CONTROLA TU VOTO<br>Computo Preliminar Electoral - Elecciones Subnacionales 2026</h2>
                        <p class="mb-0 text-white-50">Plataforma de control y registro electoral.</p>
                    </div>
                    <small class="text-white-50">Uso exclusivo de personal autorizado</small>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-panel">
                    <div class="mb-4">
                        <h4 class="mb-1 fw-bold">Iniciar Sesion</h4>
                        <small class="text-muted">Ingrese sus credenciales para continuar</small>
                    </div>

                    <form method="POST" action="{{ route('login.attempt') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Correo Electronico</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="usuario@oep.bo" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Contrasena</label>
                            <input name="password" type="password" class="form-control" placeholder="********" required>
                        </div>
                        <button class="btn btn-success btn-login w-100">Ingresar al Sistema</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
