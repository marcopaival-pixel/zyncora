@php
    $message = isset($exception) && $exception->getMessage() !== ''
        ? $exception->getMessage()
        : __('Forbidden');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title>403 — {{ config('app.name') }}</title>
    <style>
        :root {
            --bg-0: #0b0f1a;
            --bg-1: #0f172a;
            --accent: #8b5cf6;
            --accent-2: #d946ef;
            --glass: rgba(15, 23, 42, 0.72);
            --border: rgba(255, 255, 255, 0.08);
            --text: #f1f5f9;
            --muted: #94a3b8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text);
            background-color: var(--bg-1);
            background-image:
                radial-gradient(ellipse 80% 60% at 10% -10%, rgba(139, 92, 246, 0.35), transparent 55%),
                radial-gradient(ellipse 70% 50% at 100% 100%, rgba(217, 70, 239, 0.2), transparent 50%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(15, 23, 42, 0.5), transparent 70%);
            background-attachment: fixed;
        }
        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            width: 100%;
            max-width: 28rem;
            padding: 2.5rem 2rem;
            border-radius: 1.75rem;
            background: var(--glass);
            border: 1px solid var(--border);
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.55),
                0 0 0 1px rgba(139, 92, 246, 0.12) inset;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            text-align: center;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #c4b5fd;
            background: rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(139, 92, 246, 0.35);
            margin-bottom: 1.25rem;
        }
        .code {
            font-size: clamp(4rem, 14vw, 5.5rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.06em;
            margin: 0 0 1rem;
            background: linear-gradient(135deg, #a78bfa 0%, #e879f9 45%, #c084fc 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 40px rgba(139, 92, 246, 0.35));
        }
        .title {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0 0 0.75rem;
            color: #f8fafc;
        }
        .message {
            font-size: 0.95rem;
            line-height: 1.55;
            color: var(--muted);
            margin: 0 0 2rem;
        }
        .actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        @media (min-width: 420px) {
            .actions { flex-direction: row; justify-content: center; flex-wrap: wrap; }
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.35rem;
            border-radius: 0.875rem;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn:active { transform: scale(0.98); }
        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #c026d3 100%);
            box-shadow: 0 10px 30px -8px rgba(139, 92, 246, 0.55);
            border: none;
        }
        .btn-primary:hover {
            box-shadow: 0 14px 36px -6px rgba(217, 70, 239, 0.45);
        }
        .btn-ghost {
            color: var(--muted);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover {
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.15);
        }
        .glow {
            position: fixed;
            width: 24rem;
            height: 24rem;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            pointer-events: none;
            z-index: -1;
        }
        .glow-a { top: -5rem; left: -5rem; background: #6d28d9; }
        .glow-b { bottom: -8rem; right: -5rem; background: #be185d; }
    </style>
</head>
<body>
    <div class="glow glow-a" aria-hidden="true"></div>
    <div class="glow glow-b" aria-hidden="true"></div>
    <div class="wrap">
        <div class="card" role="alert">
            <div class="badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9-6a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Acesso restrito
            </div>
            <p class="code">403</p>
            <p class="title">Não foi possível continuar</p>
            <p class="message">{{ e($message) }}</p>
            <div class="actions">
                <a class="btn btn-primary" href="{{ url('/admin') }}">Voltar ao painel</a>
                <a class="btn btn-ghost" href="{{ url('/') }}">Página inicial</a>
            </div>
        </div>
    </div>
</body>
</html>
