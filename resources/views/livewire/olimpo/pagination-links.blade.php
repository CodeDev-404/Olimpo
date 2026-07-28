@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="px-2 py-1 text-xs text-ink-300 rounded border border-ink-100 dark:border-ink-700 bg-[#f4f6f9] dark:bg-white/[0.06]">Anterior</span>
            @else
                <button wire:click="previousPage" class="px-2 py-1 text-xs text-ink-600 dark:text-ink-400 rounded border border-ink-200 dark:border-ink-600 hover:bg-ink-50 dark:hover:bg-white/[0.06]">Anterior</button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 py-1 text-xs text-ink-300">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-2 py-1 text-xs font-semibold text-white bg-ink-700 rounded">{{ $page }}</span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="px-2 py-1 text-xs text-ink-600 dark:text-ink-400 rounded border border-ink-200 dark:border-ink-600 hover:bg-ink-50 dark:hover:bg-white/[0.06]">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" class="px-2 py-1 text-xs text-ink-600 dark:text-ink-400 rounded border border-ink-200 dark:border-ink-600 hover:bg-ink-50 dark:hover:bg-white/[0.06]">Siguiente</button>
            @else
                <span class="px-2 py-1 text-xs text-ink-300 rounded border border-ink-100 dark:border-ink-700 bg-[#f4f6f9] dark:bg-white/[0.06]">Siguiente</span>
            @endif
        </div>
        <span class="text-xs text-ink-400">
            {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </span>
    </nav>
@endif
