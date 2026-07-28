<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="font-size: 115%">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OLIMPO — {{ $title ?? 'Error' }}</title>
    <script>if (localStorage.getItem('dark') === 'true') document.documentElement.classList.add('dark')</script>
    <link rel="icon" type="image/jpeg" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @php
        $cssUrl = '';
        try {
            $manifestPath = public_path('build/manifest.json');
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                $cssEntry = $manifest['resources/css/app.css'] ?? null;
                if ($cssEntry) {
                    $cssUrl = asset('build/' . ($cssEntry['file'] ?? ''));
                }
            }
        } catch (\Exception $e) {}
    @endphp
    @if($cssUrl)
        <link rel="stylesheet" href="{{ $cssUrl }}">
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .dark body { background: #09090b; }
        .error-box {
            max-width: 440px;
            width: 100%;
            padding: 48px 40px;
            border-radius: 16px;
            text-align: center;
        }
        .dark .error-box {
            background: linear-gradient(135deg, rgba(18,18,28,.8), rgba(18,18,28,.6));
            backdrop-filter: blur(28px);
            border: 1px solid rgba(255,255,255,.05);
            box-shadow: 0 8px 32px rgba(0,0,0,.4);
        }
        .error-box::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(139,92,246,.3), transparent);
        }
        .light .error-box {
            background: white;
            border: 1px solid #e4e4e7;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
        }
        .error-box { position: relative; }
        .error-code {
            font-family: 'Poppins', sans-serif;
            font-size: 96px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -4px;
            margin-bottom: 4px;
        }
        .dark .error-code {
            background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .light .error-code { color: #7c3aed; }
        .error-icon { font-size: 48px; line-height: 1; margin-bottom: 8px; }
        .error-name {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .dark .error-name { color: #f4f4f5; }
        .light .error-name { color: #18181b; }
        .error-divider {
            width: 40px;
            height: 2px;
            border-radius: 1px;
            margin: 12px auto 16px;
        }
        .dark .error-divider { background: linear-gradient(90deg, #7c3aed, transparent); }
        .light .error-divider { background: linear-gradient(90deg, #7c3aed, #c084fc); }
        .error-desc {
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .dark .error-desc { color: #a1a1aa; }
        .light .error-desc { color: #71717a; }
        .error-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 24px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
        }
        .dark .btn-primary {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            box-shadow: 0 4px 15px rgba(124,58,237,.25);
        }
        .light .btn-primary {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-1px); }
        .dark .btn-primary:hover { box-shadow: 0 6px 20px rgba(124,58,237,.35); }
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
        }
        .dark .btn-ghost {
            background: rgba(255,255,255,.05);
            color: #a1a1aa;
            border-color: rgba(255,255,255,.08);
        }
        .light .btn-ghost {
            background: #f4f4f5;
            color: #71717a;
            border-color: #e4e4e7;
        }
        .dark .btn-ghost:hover { background: rgba(255,255,255,.08); color: #f4f4f5; }
        .light .btn-ghost:hover { background: #e4e4e7; color: #18181b; }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
            transition: all .2s;
            z-index: 50;
        }
        .dark .theme-toggle { background: rgba(255,255,255,.05); color: #a1a1aa; }
        .light .theme-toggle { background: #f4f4f5; color: #71717a; }
        .theme-toggle:hover { transform: scale(1.1); }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()" title="Alternar modo">🌙</button>

    <x-logo href="{{ route('olimpo.dashboard') }}" class="justify-center mb-8" />

    <div class="error-box">
        <div class="error-icon">{{ $icon }}</div>
        <div class="error-code">{{ $code }}</div>
        <div class="error-name">{{ $title }}</div>
        <div class="error-divider"></div>
        <div class="error-desc">{{ $description }}</div>
        <div class="error-actions">
            {{ $slot }}
        </div>
    </div>

    <p class="text-xs" style="margin-top: 32px; color: #52525b;">OLIMPO &middot; {{ $code }} {{ $title }}</p>

    <script>
        function toggleTheme() {
            var html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('dark', html.classList.contains('dark'));
            document.querySelector('.theme-toggle').textContent = html.classList.contains('dark') ? '☀️' : '🌙';
        }
        (function() {
            var btn = document.querySelector('.theme-toggle');
            btn.textContent = document.documentElement.classList.contains('dark') ? '☀️' : '🌙';
        })();
    </script>
</body>
</html>
