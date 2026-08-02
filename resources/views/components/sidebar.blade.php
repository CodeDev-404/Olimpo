@php
    $allPanels = config('olimpo.panels');
    $routeName = request()->route()?->getName();

    $groups = [];
    foreach ($allPanels as $route => $p) {
        if (($p['admin_only'] ?? false) && auth()->user()?->role !== 'admin') continue;
        if (($p['sidebar'] ?? true) === false) continue;
        $p['_route'] = $route;
        $groups[$p['group']][] = $p;
    }
@endphp

<aside class="sidebar w-[216px] bg-white dark:bg-[#141e36] text-ink-600 dark:text-white/70 flex flex-col h-full shrink-0 fixed lg:relative z-40">
    <div class="px-5 h-16 lg:h-20 flex items-center border-b border-ink-200 dark:border-white/[0.06] shrink-0">
        <x-logo darkBg="bg-[#4B6FE0]" />
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-0.5">
    @foreach($groups as $groupName => $items)
        <div class="text-[10px] font-semibold text-ink-400 dark:text-white/30 uppercase tracking-widest px-3 pt-5 pb-1.5 font-label">{{ $groupName }}</div>
        @foreach($items as $p)
            @if(!empty($p['children']))
                @php
                    $active = in_array($routeName, $p['children']);
                    $childPaths = collect($p['children'])
                        ->filter(fn($r) => isset($allPanels[$r]))
                        ->map(fn($r) => route($r, [], false))
                        ->values()
                        ->toJson();
                @endphp
                <div x-data="{ open: {{ $active ? 'true' : 'false' }} }"
                    x-init="const sync = () => { open = {{ $childPaths }}.some(p => location.pathname.startsWith(p)); }; sync(); window.addEventListener('livewire:navigated', sync);">
                    <button type="button" @click="open = !open" data-sidebar-parent
                        class="flex items-center gap-2 lg:gap-3 w-full px-2.5 lg:px-3 py-1.5 lg:py-2 text-xs lg:text-sm rounded-lg transition-all duration-150 text-left {{ $active ? 'text-[#5D87FF] dark:text-[#7FA6FF] bg-[#5D87FF]/10 font-semibold' : 'text-ink-600 hover:text-ink-900 hover:bg-ink-100 dark:text-white/60 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                        <i data-lucide="{{ $p['icon'] }}" class="w-4 h-4 lg:w-5 lg:h-5 shrink-0"></i>
                        <span class="truncate flex-1">{{ $p['title'] }}</span>
                        <svg x-show="!open" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-50"><path d="m9 18 6-6-6-6"/></svg>
                        <svg x-show="open" x-cloak width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-50"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="mt-1 space-y-0.5 ml-5 lg:ml-6 border-l border-ink-200 dark:border-white/10 pl-2">
                        @foreach($p['children'] as $childRoute)
                            @php $child = $allPanels[$childRoute] ?? null; @endphp
                            @if($child)
                            <a href="{{ route($childRoute) }}" wire:navigate data-sidebar-link
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs lg:text-sm transition-all duration-150 {{ $routeName === $childRoute ? 'text-[#5D87FF] dark:text-[#7FA6FF] bg-[#5D87FF]/10 font-semibold' : 'text-ink-600 hover:text-ink-900 hover:bg-ink-100 dark:text-white/60 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                                <span class="w-1 h-1 rounded-full bg-current opacity-50 shrink-0"></span>
                                <span class="truncate">{{ $child['title'] }}</span>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
            <a href="{{ route($p['_route']) }}" wire:navigate
                data-sidebar-link
                class="flex items-center gap-2 lg:gap-3 px-2.5 lg:px-3 py-1.5 lg:py-2 text-xs lg:text-sm rounded-lg transition-all duration-150 {{ $routeName === $p['_route'] ? 'text-[#5D87FF] dark:text-[#7FA6FF] bg-[#5D87FF]/10 font-semibold' : 'text-ink-600 hover:text-ink-900 hover:bg-ink-100 dark:text-white/60 dark:hover:text-white dark:hover:bg-white/[0.06]' }}">
                <i data-lucide="{{ $p['icon'] }}" class="w-4 h-4 lg:w-5 lg:h-5 shrink-0"></i>
                <span class="truncate">{{ $p['title'] }}</span>
            </a>
            @endif
        @endforeach
        @if(!$loop->last)
        <div class="h-px bg-ink-200 dark:bg-white/[0.06] mx-3 my-2"></div>
        @endif
    @endforeach
    </nav>
</aside>

<style>
.sidebar { left: -100%; transition: left 0.3s ease-out; }
.sidebar.open { left: 0; }
@media (min-width: 768px) and (max-width: 1023px) {
    .sidebar { left: -100%; position: fixed; }
    .sidebar.open { left: 0; }
}
@media (min-width: 1024px) {
    .sidebar { left: auto; position: relative; transition: none; width: 216px; }
}
</style>

<div onclick="document.querySelector('.sidebar').classList.toggle('open'); var ov = document.querySelector('.sidebar-overlay'); if (ov) { ov.classList.toggle('hidden'); }" class="sidebar-overlay fixed inset-0 bg-black/50 z-30 hidden lg:hidden" style="z-index: 35;"></div>
