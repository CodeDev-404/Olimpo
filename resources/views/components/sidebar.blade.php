@php
    $currentRoute = request()->route()?->getName();
    $allPanels = config('olimpo.panels');

    $groups = [];
    foreach ($allPanels as $route => $p) {
        if (($p['admin_only'] ?? false) && auth()->user()?->role !== 'admin') continue;
        if (($p['sidebar'] ?? true) === false) continue;
        $p['_route'] = $route;
        $groups[$p['group']][] = $p;
    }
@endphp

<aside class="sidebar w-60 bg-white dark:bg-[#1C1F2E] text-ink-600 dark:text-white/70 flex flex-col h-full shrink-0 fixed lg:relative z-40">
    <div class="px-5 h-20 flex items-center border-b border-ink-200 dark:border-white/[0.06] shrink-0">
        <x-logo darkBg="bg-[#4B6FE0]" />
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-0.5">
    @foreach($groups as $groupName => $items)
        <div class="text-[10px] font-semibold text-ink-400 dark:text-white/30 uppercase tracking-widest px-3 pt-5 pb-1.5 font-label">{{ $groupName }}</div>
        @foreach($items as $p)
        <a href="{{ route($p['_route']) }}" wire:navigate
            class="flex items-center gap-3 px-3 py-2 text-sm rounded-lg transition-all duration-150 {{ $currentRoute === $p['_route'] ? 'bg-[#5D87FF] text-white font-semibold shadow-sm' : 'text-ink-600 hover:text-ink-900 hover:bg-ink-100 dark:text-white/60 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
            <i data-lucide="{{ $p['icon'] }}" class="w-5 h-5 shrink-0"></i>
            <span>{{ $p['title'] }}</span>
        </a>
        @endforeach
        @if(!$loop->last)
        <div class="h-px bg-ink-200 dark:bg-white/[0.06] mx-3 my-2"></div>
        @endif
    @endforeach
    </nav>
</aside>

<style>
.sidebar { left: -240px; transition: left 0.3s ease-out; }
.sidebar.open { left: 0; }
@media (min-width: 1024px) {
    .sidebar { left: auto; position: relative; transition: none; }
}
</style>

<div onclick="document.querySelector('.sidebar').classList.toggle('open'); var ov = document.querySelector('.sidebar-overlay'); if (ov) { ov.classList.toggle('hidden'); }" class="sidebar-overlay fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>
