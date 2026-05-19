<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | OVH Management</title>
    @include('partials.tonasa-meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --ink: #172235;
            --muted: #64748b;
            --line: #dbe3ec;
            --surface: #ffffff;
            --canvas: #f3f6f8;
            --brand: #8b1f2c;
            --brand-deep: #661522;
            --brand-soft: #f6eaed;
            --blue: #1f5f8b;
            --success: #287a5b;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background:
                linear-gradient(180deg, rgba(139, 31, 44, .08), rgba(139, 31, 44, 0) 220px),
                var(--canvas);
            color: var(--ink);
            font-family: "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif;
        }

        .error-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .error-panel {
            width: min(100%, 860px);
            min-height: 390px;
            display: grid;
            grid-template-columns: minmax(250px, 290px) minmax(0, 1fr);
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            box-shadow: 0 24px 60px rgba(23, 34, 53, .12);
            overflow: hidden;
            animation: panel-enter .5s cubic-bezier(.2, .8, .2, 1) both;
        }

        .error-visual {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
            padding: 28px;
            color: #fff;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, .12), rgba(255, 255, 255, 0) 42%),
                linear-gradient(160deg, var(--brand) 0%, var(--brand-deep) 100%);
        }

        .error-visual::after {
            content: "";
            position: absolute;
            inset: auto 28px 28px;
            height: 1px;
            background: rgba(255, 255, 255, .18);
        }

        .error-brand {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 10px;
            min-height: 42px;
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 8px;
            background: rgba(255, 255, 255, .96);
        }

        .error-brand img {
            display: block;
            width: auto;
            height: 24px;
            object-fit: contain;
        }

        .error-brand span {
            width: 1px;
            height: 22px;
            background: #dfe4ea;
        }

        .error-scene {
            position: relative;
            height: 168px;
            display: grid;
            place-items: center;
        }

        .error-sheet {
            position: relative;
            width: 120px;
            height: 146px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 18px 28px rgba(50, 9, 17, .24);
            animation: sheet-float 3.4s ease-in-out infinite;
        }

        .error-sheet::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 34px;
            height: 34px;
            border-radius: 0 8px 0 8px;
            background: #f1dfe3;
        }

        .error-sheet::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            top: 54px;
            height: 54px;
            background:
                linear-gradient(#d7dee8, #d7dee8) 0 0 / 100% 8px no-repeat,
                linear-gradient(#d7dee8, #d7dee8) 0 22px / 76% 8px no-repeat,
                linear-gradient(#d7dee8, #d7dee8) 0 44px / 58% 8px no-repeat;
        }

        .error-orbit {
            position: absolute;
            width: 178px;
            height: 178px;
            border: 1px dashed rgba(255, 255, 255, .28);
            border-radius: 50%;
            animation: orbit-spin 10s linear infinite;
        }

        .error-orbit::before,
        .error-orbit::after {
            content: "";
            position: absolute;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
        }

        .error-orbit::before {
            top: 12px;
            right: 23px;
        }

        .error-orbit::after {
            bottom: 18px;
            left: 18px;
            opacity: .55;
        }

        .error-code {
            position: absolute;
            right: 18px;
            bottom: 18px;
            display: grid;
            place-items: center;
            min-width: 70px;
            min-height: 54px;
            padding: 0 12px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 8px;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0;
            backdrop-filter: blur(8px);
            animation: code-pulse 2.6s ease-in-out infinite;
        }

        .error-status {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, .8);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .error-status::before {
            content: "";
            width: 28px;
            height: 4px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .9);
            animation: status-scan 1.8s ease-in-out infinite;
        }

        .error-content {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(28px, 5vw, 48px);
        }

        .error-content::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--brand), var(--blue), var(--success));
            transform-origin: left;
            animation: line-grow .9s ease-out both;
        }

        .error-kicker {
            margin: 0 0 12px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        h1 {
            max-width: 560px;
            margin: 0;
            font-size: clamp(28px, 4vw, 40px);
            line-height: 1.08;
            letter-spacing: 0;
        }

        .error-copy {
            max-width: 520px;
            margin: 16px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .error-actions {
            margin-top: 28px;
        }

        .error-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border: 1px solid var(--brand);
            border-radius: 8px;
            color: var(--brand);
            background: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .error-link:hover {
            background: var(--brand-soft);
        }

        @keyframes panel-enter {
            from {
                opacity: 0;
                transform: translateY(16px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes line-grow {
            from {
                transform: scaleX(0);
            }

            to {
                transform: scaleX(1);
            }
        }

        @keyframes sheet-float {
            0%,
            100% {
                transform: translateY(0) rotate(-2deg);
            }

            50% {
                transform: translateY(-10px) rotate(2deg);
            }
        }

        @keyframes orbit-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes code-pulse {
            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes status-scan {
            0%,
            100% {
                opacity: .55;
                transform: scaleX(.7);
            }

            50% {
                opacity: 1;
                transform: scaleX(1);
            }
        }

        @media (max-width: 720px) {
            .error-shell {
                padding: 16px;
            }

            .error-panel {
                min-height: 0;
                grid-template-columns: 1fr;
            }

            .error-visual {
                min-height: 250px;
                padding: 22px;
            }

            .error-visual::after {
                inset: auto 22px 22px;
            }

            .error-scene {
                height: 126px;
            }

            .error-sheet {
                width: 96px;
                height: 116px;
            }

            .error-sheet::before {
                width: 28px;
                height: 28px;
            }

            .error-sheet::after {
                left: 15px;
                right: 15px;
                top: 42px;
                height: 46px;
                background:
                    linear-gradient(#d7dee8, #d7dee8) 0 0 / 100% 7px no-repeat,
                    linear-gradient(#d7dee8, #d7dee8) 0 19px / 76% 7px no-repeat,
                    linear-gradient(#d7dee8, #d7dee8) 0 38px / 58% 7px no-repeat;
            }

            .error-orbit {
                width: 140px;
                height: 140px;
            }

            .error-code {
                min-width: 64px;
                min-height: 48px;
                font-size: 22px;
            }

            .error-content {
                padding: 26px 22px 28px;
            }

            h1 {
                font-size: clamp(24px, 8vw, 32px);
            }

            .error-copy {
                font-size: 15px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-panel,
            .error-content::before,
            .error-sheet,
            .error-orbit,
            .error-code,
            .error-status::before {
                animation: none;
            }
        }
    </style>
</head>
<body>
    <main class="error-shell">
        <section class="error-panel" role="status" aria-live="polite">
            <aside class="error-visual">
                <div class="error-brand" aria-hidden="true">
                    <img src="{{ asset('assets/images/logo/logo-sig.png') }}" alt="">
                    <span></span>
                    <img src="{{ asset('assets/images/logo/logo-st2.png') }}" alt="">
                </div>

                <div class="error-scene" aria-hidden="true">
                    <div class="error-orbit"></div>
                    <div class="error-sheet"></div>
                    <div class="error-code">@yield('code')</div>
                </div>

                <div class="error-status">@yield('kicker')</div>
            </aside>

            <div class="error-content">
                <p class="error-kicker">@yield('kicker')</p>
                <h1>@yield('heading')</h1>
                <p class="error-copy">@yield('message')</p>

                @hasSection('action')
                    <div class="error-actions">
                        @yield('action')
                    </div>
                @endif
            </div>
        </section>
    </main>
</body>
</html>
