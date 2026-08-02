<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>OLIMPO — {{ config('app.name', 'Laravel') }}</title>

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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            @media (max-width: 767px) {
                body { padding-left: 20px; padding-right: 20px; }
            }
        </style>
    </head>
    <body class="font-sans antialiased text-ink-900 bg-[#f6f8fa] dark:from-ink-950 dark:to-ink-900 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div>
            <a href="/" wire:navigate class="block mx-auto w-fit">
                <img src="{{ asset('logo.png') }}"
                     alt="OLIMPO"
                     class="w-16 h-16 rounded-full bg-[#5D87FF] mx-auto mb-2 shadow-lg shadow-[#5D87FF]/20"
                     style="object-fit: cover;">
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-4 px-6 py-5 bg-white dark:bg-[#141e36] rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
            {{ $slot }}
        </div>

        <p class="mt-6 text-xs text-ink-400 dark:text-ink-500">Sistema de Control — OLIMPO</p>

        @livewireScripts
    </body>
</html>
