<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CONTROLA TU VOTO | Computo Preliminar Electoral - Elecciones Subnacionales 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .app-tabs-wrap {
            background: #ffffff;
            border-bottom: 1px solid #dfe3e8;
        }

        .app-tabs {
            display: flex;
            gap: 0;
            overflow-x: auto;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .app-tabs .tab-link {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            min-width: 180px;
            justify-content: center;
            padding: .75rem 1rem;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: none;
            border: 1px solid #d2d8df;
            border-bottom: none;
            border-radius: .45rem .45rem 0 0;
            color: #344054;
            background: #eef1f5;
            transition: .15s ease-in-out;
        }

        .app-tabs .tab-link.active {
            background: #b1061a;
            border-color: #b1061a;
            color: #fff;
        }

        .app-tabs .tab-link:not(.active):hover {
            background: #e3e8ef;
            color: #1d2939;
        }

        @media (max-width: 576px) {
            .app-tabs .tab-link {
                min-width: 145px;
                font-size: .86rem;
            }
        }

        .live-clock-wrap {
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .live-clock {
            font-size: .92rem;
            color: #1f2937;
            font-weight: 600;
        }

        .live-clock-label {
            color: #6b7280;
            font-weight: 500;
            margin-right: .4rem;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">CONTROLA TU VOTO</a>
        @auth
            <div class="d-flex align-items-center gap-2 text-white">
                @if(auth()->user()->isAdmin())
                    <a class="btn btn-sm btn-outline-light" href="{{ route('admin.users.index') }}">Usuarios</a>
                    <a class="btn btn-sm btn-outline-light" href="{{ route('admin.habilitaciones.index') }}">Habilitaciones</a>
                @endif
                <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">Salir</button>
                </form>
            </div>
        @endauth
    </div>
</nav>

@auth
    <div class="app-tabs-wrap">
        <div class="container pt-2">
            <ul class="app-tabs">
                <li>
                    <a href="{{ route('dashboard') }}" class="tab-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span>RESULTADOS</span>
                    </a>
                </li>
                @if(auth()->user()->isAdmin() || auth()->user()->isOperator() || auth()->user()->isViewer())
                    <li>
                        <a href="{{ route('actas.index') }}" class="tab-link {{ request()->routeIs('actas.*') ? 'active' : '' }}">
                            <span>ACTAS</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    @if(request()->routeIs('dashboard') || request()->routeIs('actas.index'))
        <div class="live-clock-wrap">
            <div class="container py-2 d-flex justify-content-end">
                <div class="live-clock" id="liveClock">
                    <span class="live-clock-label">Ultima actualizacion de datos:</span>
                    <span id="liveClockValue"></span>
                </div>
            </div>
        </div>
    @endif
@endauth

<main class="py-4">
    @yield('content')
</main>

<script>
(() => {
    const clockEl = document.getElementById('liveClockValue');
    if (!clockEl) return;

    const formatter = new Intl.DateTimeFormat('es-BO', {
        timeZone: 'America/La_Paz',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });

    clockEl.textContent = formatter.format(new Date());
})();
</script>
</body>
</html>
