<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="font-size: 115%">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OLIMPO — {{ $title ?? 'Error' }}</title>
    <script data-navigate-track>
        if (localStorage.getItem('dark') === 'true') document.documentElement.classList.add('dark');
    </script>
    <script>
        document.addEventListener('livewire:navigated', function () {
            if (localStorage.getItem('dark') === 'true') document.documentElement.classList.add('dark');
        });
    </script>
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
        html, body { min-height: 100vh; min-height: 100svh; }

        html { background-color: #f6f8fa; }
        html.dark { background-color: #0b1120; }

        body {
            background-color: #f6f8fa;
            transition: background-color .25s;
        }
        .dark body { background-color: #0b1120; }

        @media (max-width: 767px) {
            body { padding-left: 20px; padding-right: 20px; }
        }

        .error-box { position: relative; text-align: center; }
        .error-box::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(139,92,246,.3), transparent);
        }

        .error-icon { font-size: 48px; line-height: 1; margin-bottom: 8px; }

        .error-code {
            font-family: 'Poppins', sans-serif;
            font-size: 96px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -4px;
            margin-bottom: 4px;
            color: #7c3aed;
        }
        .dark .error-code {
            background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .error-name {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #18181b;
            transition: color .25s;
        }
        .dark .error-name { color: #f4f4f5; }

        .error-divider {
            width: 40px;
            height: 2px;
            border-radius: 1px;
            margin: 12px auto 16px;
            background: linear-gradient(90deg, #7c3aed, #c084fc);
        }
        .dark .error-divider { background: linear-gradient(90deg, #7c3aed, transparent); }

        .error-desc {
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 28px;
            color: #71717a;
            transition: color .25s;
        }
        .dark .error-desc { color: #a1a1aa; }

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
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            box-shadow: 0 4px 15px rgba(124,58,237,.25);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(124,58,237,.35); }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #e4e4e7;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            background: #f4f4f5;
            color: #71717a;
        }
        .btn-ghost:hover { background: #e4e4e7; color: #18181b; }
        .dark .btn-ghost {
            background: rgba(255,255,255,.05);
            color: #a1a1aa;
            border-color: rgba(255,255,255,.08);
        }
        .dark .btn-ghost:hover { background: rgba(255,255,255,.08); color: #f4f4f5; }

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
            background: white;
            color: #71717a;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }
        .dark .theme-toggle { background: rgba(255,255,255,.05); color: #a1a1aa; box-shadow: none; }
        .theme-toggle:hover { transform: scale(1.1); }
    </style>
</head>
<body class="font-sans antialiased text-ink-900 bg-[#f6f8fa] min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    <button class="theme-toggle" onclick="toggleTheme()" title="Alternar modo">🌙</button>

    <a href="{{ route('olimpo.dashboard') }}" class="block mx-auto w-fit">
        <img src="{{ asset('logo.png') }}"
             alt="OLIMPO"
             class="w-16 h-16 rounded-full bg-[#5D87FF] mx-auto mb-2 shadow-lg shadow-[#5D87FF]/20"
             style="object-fit: cover;">
    </a>

    <div class="w-full sm:max-w-md mt-4 px-6 py-5 bg-white dark:bg-[#141e36] rounded-xl border border-[#e5eaef] dark:border-white/[0.06] error-box">
        <div class="error-icon">{{ $icon }}</div>
        <div class="error-code">{{ $code }}</div>
        <div class="error-name">{{ $title }}</div>
        <div class="error-divider"></div>
        <div class="error-desc">{{ $description }}</div>
        <div class="error-actions">
            {{ $slot }}
        </div>
    </div>

    <p class="mt-6 text-xs text-ink-400 dark:text-ink-500">Sistema de Control — OLIMPO</p>

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
