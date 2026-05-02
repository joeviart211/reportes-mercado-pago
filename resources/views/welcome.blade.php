<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Intranet</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:400,600,700,800|jetbrains-mono:400,500&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg:      #080b10;
            --border:  rgba(255,255,255,0.07);
            --accent:  #3b82f6;
            --accent2: #06b6d4;
            --text:    #e2e8f0;
            --muted:   #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Syne', sans-serif;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ── Grid ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(59,130,246,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.04) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Orbs ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(130px);
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { width: 650px; height: 650px; background: #1d4ed8; opacity: 0.15; top: -250px; left: -180px; }
        .orb-2 { width: 420px; height: 420px; background: #0891b2; opacity: 0.12; bottom: -120px; right: -80px; }

        /* ── Nav ── */
        nav {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.375rem 2.5rem;
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(16px);
            background: rgba(8,11,16,0.6);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .brand-icon {
            width: 30px; height: 30px;
            border-radius: 7px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
        }
        .brand-icon svg { width: 16px; height: 16px; color: #fff; }

        .brand-name {
            font-size: 0.9375rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text);
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            background: var(--accent);
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 0.02em;
            transition: all 0.2s;
        }
        .btn-nav:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(59,130,246,0.4);
        }

        /* ── Main ── */
        main {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 2rem;
            gap: 2rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.875rem;
            border-radius: 999px;
            border: 1px solid rgba(6,182,212,0.3);
            background: rgba(6,182,212,0.07);
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--accent2);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
            animation: fadeDown 0.6s ease both;
        }
        .badge .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent2);
            animation: pulse 2s infinite;
        }

        h1 {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 800;
            line-height: 1.04;
            letter-spacing: -0.035em;
            animation: fadeUp 0.7s 0.1s ease both;
            opacity: 0;
        }
        .grad {
            background: linear-gradient(100deg, var(--accent) 30%, var(--accent2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sub {
            max-width: 460px;
            font-size: 1.0625rem;
            color: var(--muted);
            line-height: 1.7;
            font-weight: 400;
            animation: fadeUp 0.7s 0.2s ease both;
            opacity: 0;
        }

        .cta {
            animation: fadeUp 0.7s 0.3s ease both;
            opacity: 0;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2.25rem;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 0.02em;
            transition: all 0.25s;
            box-shadow: 0 0 0 1px rgba(59,130,246,0.5), 0 8px 32px rgba(59,130,246,0.25);
        }
        .btn-cta:hover {
            background: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 0 0 1px rgba(59,130,246,0.6), 0 20px 48px rgba(59,130,246,0.4);
        }
        .btn-cta svg { width: 18px; height: 18px; transition: transform 0.2s; }
        .btn-cta:hover svg { transform: translateX(3px); }

        .strip {
            display: flex;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255,255,255,0.02);
            backdrop-filter: blur(8px);
            animation: fadeUp 0.7s 0.4s ease both;
            opacity: 0;
        }

        .strip-item {
            padding: 1rem 2rem;
            border-right: 1px solid var(--border);
            text-align: center;
        }
        .strip-item:last-child { border-right: none; }

        .strip-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--text);
            line-height: 1;
        }
        .strip-label {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 0.375rem;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            font-weight: 600;
        }

        footer {
            position: relative;
            z-index: 1;
            padding: 1rem 2.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            color: var(--muted);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }
    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <nav>
        <div class="brand">
            <div class="brand-icon">
                <!--<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg> -->
            </div>
            <!-- <span class="brand-name">Intranet</span> -->
        </div>

        @if (Route::has('login'))
            <a href="{{ route('login') }}" class="btn-nav">
                Iniciar sesión
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        @endif
    </nav>

    <main>
        <!-- <div class="badge">
            <span class="dot"></span>
            Acceso restringido
        </div> -->

        <h1>
            Bienvenido <br>
            <!-- <span class="grad">tu intranet</span> -->
        </h1>

        <!-- <p class="sub">
            Plataforma interna para la gestión de sucursales,
            usuarios y operaciones. Inicia sesión para continuar.
        </p> -->

        <div class="cta">
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="btn-cta">
                    Iniciar sesión
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            @endif
        </div>

        <div class="strip">
            <div class="strip-item">
                <div class="strip-val">{{ now()->format('H:i') }}</div>
                <div class="strip-label">Hora</div>
            </div>
            <div class="strip-item">
                <div class="strip-val">{{ now()->format('d/m/Y') }}</div>
                <div class="strip-label">Fecha</div>
            </div>
            <div class="strip-item">
                <div class="strip-val" style="font-size:1rem;padding-top:0.2rem;text-transform:capitalize">
                    {{ now()->locale('es')->dayName }}
                </div>
                <div class="strip-label">Día</div>
            </div>
        </div>
    </main>

    <footer>
        <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }} · PHP v{{ PHP_VERSION }}</span>
        <span>© {{ now()->year }} · Uso interno</span>
    </footer>

</body>
</html>
