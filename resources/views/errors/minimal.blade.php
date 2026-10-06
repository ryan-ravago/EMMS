<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>@yield('title') | {{ config('app.name') }}</title>
        @yield('head')

        {{-- Self-contained on purpose: no Vite, no database, no external fonts, so it still renders when the app is struggling. --}}
        <style>
            :root {
                --bg: #f3f5fa;
                --card: #fff;
                --text: #111827;
                --muted: #5b6475;
                --border: #e3e7ef;
                --soft: #eef2ff;
                --primary: #2563eb;
                --primary-hover: #1d4ed8;
                --primary-text: #fff;
                --warning: #d97706;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --bg: #0b1020;
                    --card: #121a2e;
                    --text: #f1f5f9;
                    --muted: #9aa6bd;
                    --border: #243049;
                    --soft: #172138;
                    --primary: #3b82f6;
                    --primary-hover: #60a5fa;
                    --primary-text: #0b1020;
                    --warning: #fbbf24;
                }
            }

            * { box-sizing: border-box; }
            [hidden] { display: none !important; }

            body {
                display: grid;
                place-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 1.5rem;
                background: var(--bg);
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.5;
                -webkit-font-smoothing: antialiased;
            }

            .card {
                width: 100%;
                max-width: 30rem;
                padding: 2.5rem 2rem;
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: 1rem;
                box-shadow: 0 10px 30px rgba(15, 23, 42, .07);
                text-align: center;
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: .5rem;
                color: var(--muted);
                font-size: .9rem;
                font-weight: 600;
                letter-spacing: .02em;
            }

            .brand svg { width: 1.5rem; height: 1.5rem; color: var(--primary); }

            .code {
                margin: 1.75rem 0 .25rem;
                color: var(--primary);
                font-size: 4.5rem;
                font-weight: 700;
                letter-spacing: -.03em;
                line-height: 1;
            }

            h1 { margin: .5rem 0 .75rem; font-size: 1.5rem; line-height: 1.3; }
            .description { margin: 0; color: var(--muted); }

            .panel {
                margin: 1.5rem 0 0;
                padding: 1rem 1.25rem;
                background: var(--soft);
                border: 1px solid var(--border);
                border-radius: .75rem;
                font-size: .9rem;
                text-align: left;
            }

            .panel dt { font-weight: 600; }
            .panel dd { margin: .15rem 0 .85rem; color: var(--muted); }
            .panel dd:last-child { margin-bottom: 0; }

            .status { display: inline-flex; align-items: center; gap: .5rem; color: var(--warning); }
            .status::before {
                content: "";
                width: .55rem;
                height: .55rem;
                border-radius: 50%;
                background: currentColor;
                animation: pulse 1.6s ease-in-out infinite;
            }

            @keyframes pulse { 50% { opacity: .35; } }
            @media (prefers-reduced-motion: reduce) { .status::before { animation: none; } }

            .actions { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: center; margin-top: 2rem; }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2.75rem;
                padding: .6rem 1.25rem;
                border: 1px solid transparent;
                border-radius: .6rem;
                font: inherit;
                font-size: .95rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                transition: background-color .15s, border-color .15s;
            }

            .btn:focus-visible { outline: 3px solid var(--primary); outline-offset: 2px; }
            .btn-primary { background: var(--primary); color: var(--primary-text); }
            .btn-primary:hover { background: var(--primary-hover); }
            .btn-secondary { background: transparent; border-color: var(--border); color: var(--text); }
            .btn-secondary:hover { background: var(--soft); }

            @media (max-width: 30rem) {
                .card { padding: 2rem 1.25rem; }
                .actions .btn { width: 100%; }
            }
        </style>
    </head>
    <body>
        <main class="card">
            <div class="brand">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span>{{ config('app.name') }}</span>
            </div>

            <p class="code">@yield('code')</p>
            <h1>@yield('message')</h1>
            <p class="description">@yield('description')</p>

            @yield('extra')

            <div class="actions">
                @hasSection('actions')
                    @yield('actions')
                @else
                    <a class="btn btn-primary" href="{{ url('/') }}">{{ __('Back to home') }}</a>
                    <button type="button" class="btn btn-secondary" data-back hidden>{{ __('Go back') }}</button>
                @endif
            </div>
        </main>

        <script>
            // "Try again" is a fresh GET of the same address, so it never re-submits a form.
            document.querySelectorAll('[data-retry]').forEach(function (button) {
                button.addEventListener('click', function () { location.assign(location.href); });
            });

            // "Go back" only makes sense when there is somewhere to go back to.
            if (history.length > 1) {
                document.querySelectorAll('[data-back]').forEach(function (button) {
                    button.hidden = false;
                    button.addEventListener('click', function () { history.back(); });
                });
            }
        </script>
    </body>
</html>
