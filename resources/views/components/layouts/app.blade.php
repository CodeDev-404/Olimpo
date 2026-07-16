<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OLIMPO — {{ $title ?? 'Sistema de Control' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-ink-50 text-ink-900 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <livewire:olimpo.sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-ink-100 h-14 flex items-center justify-between px-4 lg:px-6 shrink-0">
                <div class="flex items-center gap-3">
                    <button type="button"
                        onclick="document.querySelector('.sidebar').classList.toggle('-translate-x-full'); var ov = document.querySelector('.sidebar-overlay'); if (ov) { if (document.querySelector('.sidebar').classList.contains('-translate-x-full')) { ov.classList.add('hidden'); } else { ov.classList.remove('hidden'); } }"
                        class="lg:hidden p-2 text-ink-400 hover:text-ink-600 rounded-md hover:bg-ink-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        </svg>
                    </button>
                    <h1 class="text-sm font-semibold text-ink-800 truncate">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2.5" x-data="{ now: new Date() }" x-init="setInterval(() => now = new Date(), 1000)">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                        <div class="text-right leading-tight">
                            <p class="text-sm font-semibold text-ink-800 tabular-nums -mb-0.5" x-text="now.toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'})"></p>
                            <p class="text-[11px] text-ink-400 tabular-nums" x-text="now.toLocaleDateString('es-PE', {day:'2-digit', month:'2-digit', year:'numeric'})"></p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost text-xs">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                            </svg>
                            Salir
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div
        x-data="{ show: false, message: '', type: 'success', timer: null }"
        x-on:notify.window="
            let msg = $event.detail.message || $event.detail[0]?.message || '';
            let typ = $event.detail.type || $event.detail[0]?.type || 'success';
            if (msg) {
                clearTimeout(this.timer);
                show = true; message = msg; type = typ;
                this.timer = setTimeout(() => show = false, 5000);
            }
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-[9999] px-4 py-2.5 rounded-lg shadow-[0_4px_12px_rgb(0_0_0_/_0.15)] text-sm font-semibold text-white max-w-sm"
        :class="type === 'success' ? 'bg-green-600' : (type === 'warning' ? 'bg-ember-500' : (type === 'danger' || type === 'error' ? 'bg-red-600' : 'bg-blue-600'))"
        x-text="message"
    ></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
