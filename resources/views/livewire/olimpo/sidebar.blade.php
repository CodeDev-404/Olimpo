<div>
    <aside class="sidebar w-60 bg-ink-950 text-ink-300 flex flex-col h-full shrink-0 fixed lg:static z-40 -translate-x-full lg:translate-x-0 transition-transform duration-200">
        <div class="px-4 h-14 flex items-center gap-3 border-b border-white/5 shrink-0">
            <div class="w-7 h-7 bg-ember-500 rounded flex items-center justify-center text-ink-950 font-bold text-xs">O</div>
            <div>
                <h2 class="text-sm font-bold text-white tracking-wide">OLIMPO</h2>
                <p class="text-[10px] text-ink-500">Sistema de Control</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">
            <div class="text-[10px] text-ink-600 uppercase tracking-widest px-3 pb-2 font-semibold">Navegación</div>
        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
                class="nav-link {{ $currentRoute === $item['route'] ? 'nav-link-active' : 'nav-link-inactive' }}">
                {!! $item['svg'] !!}
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
        </nav>
        <!-- CR:{{ $currentRoute ?? 'NULL' }}-->

        <div class="border-t border-white/5 px-4 py-3 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-ember-500/20 text-ember-400 rounded-full flex items-center justify-center text-[10px] font-bold uppercase">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-ink-500 capitalize">{{ auth()->user()->role }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div onclick="document.querySelector('.sidebar').classList.toggle('-translate-x-full'); var ov = document.querySelector('.sidebar-overlay'); if (ov) { ov.classList.toggle('hidden'); }" class="sidebar-overlay fixed inset-0 bg-black/60 z-30 hidden lg:hidden"></div>
</div>
