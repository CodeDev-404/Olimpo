@php
if (!function_exists('h')) {
    function h(?string $text, ?string $search = null): string {
        if ($text === null || $text === '') return '<span class="text-ink-300">—</span>';
        $text = e($text);
        if ($search) {
            foreach (explode(' ', $search) as $part) {
                $part = trim($part);
                if ($part === '') continue;
                $text = preg_replace('/(' . preg_quote($part, '/') . ')/iu', '<mark class="bg-yellow-200 text-ink-900 dark:text-ink-100 rounded px-0.5">$1</mark>', $text);
            }
        }
        return $text;
    }
}
$activeFilters = ($filterFecha ? 1 : 0) + ($filterTurno ? 1 : 0) + ($filterHoraDesde ? 1 : 0) + ($filterHoraHasta ? 1 : 0) + ($filterMesDesde ? 1 : 0) + ($filterMesHasta ? 1 : 0);
$hasAnyFilter = $search || $filterFecha || $filterHoraDesde || $filterHoraHasta || $filterTurno || $filterMesDesde || $filterMesHasta;
if ($filterFecha) {
    $calDef = explode('/', $filterFecha);
    $calD = (int)$calDef[0];
    $calM = (int)$calDef[1] - 1;
    $calY = (int)$calDef[2];
} else {
    $calD = (int)date('j');
    $calM = (int)date('n') - 1;
    $calY = (int)date('Y');
}
@endphp
<div x-data="{ popTitle: '', popContent: '', showPop: false, filtersOpen: false }" x-cloak>
    <div class="card mb-5" style="overflow:visible">
        <div class="card-body">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-[12.8rem] sm:min-w-[12.8rem]">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400"></i>
                <input type="text" wire:model.live="search" placeholder="Buscar..."
                    class="input-field pl-9 h-9 w-full" />
            </div>

            <button
                class="flex items-center gap-1.5 px-3 h-9 rounded-lg border border-[#e5eaef] dark:border-white/[0.06] bg-white dark:bg-ink-800/50 text-ink-400 dark:text-ink-500 hover:border-[#5D87FF] dark:hover:border-[#5D87FF] hover:text-[#5D87FF] dark:hover:text-[#5D87FF] transition-all duration-150 shrink-0"
                :class="{ 'border-[#5D87FF] dark:border-[#5D87FF] text-[#5D87FF] dark:text-[#5D87FF] bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': filtersOpen }"
                @click="
                    if(filtersOpen) {
                        filtersOpen = false;
                    } else {
                        filtersOpen = true;
                        $nextTick(() => $dispatch('filter-reset',{sync:1}));
                    }
                "
                :title="filtersOpen ? 'Cerrar' : 'Abrir filtros'">
                <svg x-show="!filtersOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 6V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                <span x-show="!filtersOpen" class="text-xs font-medium">Filtrar</span>
                <svg x-show="filtersOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                <span x-show="filtersOpen" class="text-xs font-medium">Cerrar</span>
            </button>

            <div x-show="!filtersOpen && {{ $hasAnyFilter ? 'true' : 'false' }}" x-cloak>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20 text-[#5D87FF] dark:text-[#5D87FF] border border-[#5D87FF]/20 dark:border-[#5D87FF]/40">
                    ● {{ $activeFilters }}
                </span>
            </div>

            <div x-show="filtersOpen" wire:ignore x-cloak class="flex flex-nowrap items-center gap-2 sm:gap-3 filter-slide-in overflow-x-auto pb-80 -mb-80">

                {{-- Fecha A2: input + botón calendario --}}
                <div class="relative" x-data="{ 
                        open: false, 
                        m: {{ $calM }}, 
                        y: {{ $calY }}, 
                        val: '{{ $filterFecha }}',
                        generateDays(y,m){const d=[];const f=new Date(y,m,1).getDay();for(let i=0;i<42;i++)d.push(new Date(y,m,1-f+i));return d},
                        fmt(d){var dd=d.getDate(),mm=d.getMonth()+1;return(dd<10?'0':'')+dd+'/'+(mm<10?'0':'')+mm+'/'+d.getFullYear()},
                        isSel(d){
                            return this.val && this.fmt(d)===this.val;
                        }
                    }"
                     @filter-reset.window="if($event.detail.reset){val='';m=(new Date()).getMonth();y=(new Date()).getFullYear()}else{if(val!==($wire.filterFecha||''))val=$wire.filterFecha||'';if(!$wire.filterFecha){m=(new Date()).getMonth();y=(new Date()).getFullYear()}}"
                     @filter-fecha-reset.window="val='';m=(new Date()).getMonth();y=(new Date()).getFullYear()">
                    <div class="flex gap-1.5">
                        <input type="text" x-model="val" @focus="open = true" @input="$wire.set('filterFecha', $el.value); if ($el.value) window.dispatchEvent(new CustomEvent('filter-mes-reset'))"
                            class="input-field w-28 h-9" placeholder="dd/mm/aaaa" />
                        <button @click="open = !open"
                            class="w-9 h-9 flex items-center justify-center rounded-lg transition-all border border-[#5D87FF]/20 dark:border-[#5D87FF]/40 bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20 text-[#5D87FF] dark:text-[#5D87FF] hover:bg-[#5D87FF]/20 dark:hover:bg-[#5D87FF]/30 cursor-pointer shrink-0">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </button>
                    </div>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1.5" style="top:100%;left:0;width:260px">
                        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] p-3">
                            <div class="flex items-center justify-between mb-3">
                                <button class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800 text-ink-400" @click="m=m===0?(y--,11):m-1">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                                </button>
                                <span class="text-xs font-medium capitalize text-ink-900 dark:text-ink-100" x-text="new Date(y,m).toLocaleDateString('es-PE',{month:'long',year:'numeric'})"></span>
                                <button class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800 text-ink-400" @click="m=m===11?(y++,0):m+1">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-7 gap-0.5 mb-1">
                                <template x-for="d in ['Do','Lu','Ma','Mi','Ju','Vi','Sa']" :key="d">
                                    <div class="text-center text-[10px] py-1 text-ink-400 dark:text-ink-500 font-medium" x-text="d"></div>
                                </template>
                            </div>
                            <div class="grid grid-cols-7 gap-0.5">
                                <template x-for="(d,i) in generateDays(y,m)" :key="i">
                                    <div class="w-[30px] h-[30px] flex items-center justify-center rounded-lg text-xs transition-all"
                                        :class="{
                                            'bg-[#5D87FF] text-white font-semibold shadow-sm': isSel(d),
                                            'bg-[#5D87FF]/20 dark:bg-[#5D87FF]/30 !text-[#5D87FF] dark:!text-[#5D87FF] font-bold ring-2 ring-[#5D87FF]/40 dark:ring-[#5D87FF]/40': !isSel(d) && fmt(d)===fmt(new Date()) && d.getMonth()===m,
                                            'text-ink-300 dark:text-ink-600 cursor-default': d.getMonth()!==m,
                                            'text-ink-600 dark:text-ink-300 cursor-pointer hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]': d.getMonth()===m
                                        }"
                                        @click="if(d.getMonth()===m){val=fmt(d);open=false;$wire.set('filterFecha',fmt(d));window.dispatchEvent(new CustomEvent('filter-mes-reset'))}" x-text="d.getDate()"></div>
                                </template>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-ink-100 dark:border-ink-700">
                                <button @click="open=false; val=''; m=(new Date()).getMonth(); y=(new Date()).getFullYear(); $wire.set('filterFecha', ''); window.dispatchEvent(new CustomEvent('filter-mes-reset'))" class="text-xs text-ink-400 hover:text-ink-600 dark:hover:text-ink-300">Limpiar</button>
                                <button @click="val=fmt(new Date()); open=false; $wire.set('filterFecha', fmt(new Date())); window.dispatchEvent(new CustomEvent('filter-mes-reset'))" class="text-xs font-medium text-[#5D87FF]">Hoy</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Turno B2: custom dropdown con color --}}
                <div x-data="{ open: false, sel: '{{ $filterTurno ?: 'Todos' }}', opts: ['Todos','DÍA','NOCHE'] }"
                     @filter-reset.window="if($event.detail.reset){sel='Todos'}else{if(sel!==($wire.filterTurno||'Todos'))sel=$wire.filterTurno||'Todos'}"
                     class="relative">
                    <div class="input-field flex items-center gap-2 cursor-pointer h-9 !px-[10px] min-w-[90px]" @click="open = !open">
                        <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: sel==='DÍA'?'#f59e0b':sel==='NOCHE'?'#6366f1':'rgba(161,161,170,0.3)' }"></span>
                        <span class="flex-1 text-xs font-medium whitespace-nowrap text-ink-700 dark:text-ink-300" x-text="sel"></span>
                        <svg :class="{ 'rotate-180': open }" class="transition-transform shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 min-w-[120px]">
                        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] py-1">
                            <template x-for="o in opts" :key="o">
                                <div class="flex items-center gap-2 px-3 py-2 text-xs cursor-pointer transition-colors text-ink-600 dark:text-ink-400 hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]"
                                    :class="{ 'text-[#5D87FF] dark:text-[#5D87FF] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': sel === o }"
                                    @click="sel = o; open = false; $wire.set('filterTurno', o === 'Todos' ? '' : o)">
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: o==='DÍA'?'#f59e0b':o==='NOCHE'?'#6366f1':'rgba(161,161,170,0.3)' }"></span>
                                    <span x-text="o"></span>
                                    <svg x-show="sel === o" class="ml-auto shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#5D87FF" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Mes desde/hasta --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wider">Mes:</span>
                    <div x-data="{ open: false, sel: '{{ $filterMesDesde }}', opts: ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] }"
                         @filter-reset.window="if($event.detail.reset){sel=''}else{if(sel!==($wire.filterMesDesde||''))sel=$wire.filterMesDesde||''}"
                         @filter-mes-reset.window="sel=''"
                         class="relative">
                        <div class="input-field flex items-center gap-2 cursor-pointer h-9 !px-[10px] min-w-[90px]" @click="open = !open">
                            <span class="flex-1 text-xs font-medium whitespace-nowrap text-ink-700 dark:text-ink-300" x-text="sel ? opts[sel] : 'Desde'"></span>
                            <svg :class="{ 'rotate-180': open }" class="transition-transform shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 min-w-[120px]">
                            <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] py-1 max-h-48 overflow-y-auto">
                                <template x-for="(o, i) in opts" :key="i">
                                    <div class="flex items-center gap-2 px-3 py-2 text-xs cursor-pointer transition-colors text-ink-600 dark:text-ink-400 hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]"
                                        :class="{ 'text-[#5D87FF] dark:text-[#5D87FF] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': sel == i }"
                                        x-show="i > 0"
                                        @click="sel = i; open = false; $wire.set('filterMesDesde', String(i)); if (i) window.dispatchEvent(new CustomEvent('filter-fecha-reset'))">
                                        <span x-text="o"></span>
                                        <svg x-show="sel == i" class="ml-auto shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#5D87FF" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <span class="text-ink-300 text-xs">—</span>
                    <div x-data="{ open: false, sel: '{{ $filterMesHasta }}', opts: ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] }"
                         @filter-reset.window="if($event.detail.reset){sel=''}else{if(sel!==($wire.filterMesHasta||''))sel=$wire.filterMesHasta||''}"
                         @filter-mes-reset.window="sel=''"
                         class="relative">
                        <div class="input-field flex items-center gap-2 cursor-pointer h-9 !px-[10px] min-w-[90px]" @click="open = !open">
                            <span class="flex-1 text-xs font-medium whitespace-nowrap text-ink-700 dark:text-ink-300" x-text="sel ? opts[sel] : 'Hasta'"></span>
                            <svg :class="{ 'rotate-180': open }" class="transition-transform shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 min-w-[120px]">
                            <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] py-1 max-h-48 overflow-y-auto">
                                <template x-for="(o, i) in opts" :key="i">
                                    <div class="flex items-center gap-2 px-3 py-2 text-xs cursor-pointer transition-colors text-ink-600 dark:text-ink-400 hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]"
                                        :class="{ 'text-[#5D87FF] dark:text-[#5D87FF] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': sel == i }"
                                        x-show="i > 0"
                                        @click="sel = i; open = false; $wire.set('filterMesHasta', String(i)); if (i) window.dispatchEvent(new CustomEvent('filter-fecha-reset'))">
                                        <span x-text="o"></span>
                                        <svg x-show="sel == i" class="ml-auto shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#5D87FF" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rango de hora --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-ink-500 dark:text-ink-400 font-semibold uppercase tracking-wider">Hora:</span>
                    <div x-data="{ open: false, sel: '{{ $filterHoraDesde }}', opts: Array.from({length:24},(_,i)=>String(i).padStart(2,'0')+':00') }"
                         @filter-reset.window="if($event.detail.reset){sel=''}else{if(sel!==($wire.filterHoraDesde||''))sel=$wire.filterHoraDesde||''}"
                         class="relative">
                        <div class="input-field flex items-center gap-2 cursor-pointer h-9 !px-[10px] min-w-[90px]" @click="open = !open">
                            <span class="flex-1 text-xs font-medium whitespace-nowrap text-ink-700 dark:text-ink-300" x-text="sel || 'Desde'"></span>
                            <svg :class="{ 'rotate-180': open }" class="transition-transform shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 min-w-[90px]">
                            <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] py-1 max-h-48 overflow-y-auto">
                                <template x-for="o in opts" :key="o">
                                    <div class="flex items-center gap-2 px-3 py-2 text-xs cursor-pointer transition-colors text-ink-600 dark:text-ink-400 hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]"
                                        :class="{ 'text-[#5D87FF] dark:text-[#5D87FF] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': sel === o }"
                                        @click="sel = o; open = false; $wire.set('filterHoraDesde', o)">
                                        <span x-text="o"></span>
                                        <svg x-show="sel === o" class="ml-auto shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#5D87FF" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <span class="text-ink-300 text-xs">—</span>
                    <div x-data="{ open: false, sel: '{{ $filterHoraHasta }}', opts: Array.from({length:24},(_,i)=>String(i).padStart(2,'0')+':00') }"
                         @filter-reset.window="if($event.detail.reset){sel=''}else{if(sel!==($wire.filterHoraHasta||''))sel=$wire.filterHoraHasta||''}"
                         class="relative">
                        <div class="input-field flex items-center gap-2 cursor-pointer h-9 !px-[10px] min-w-[90px]" @click="open = !open">
                            <span class="flex-1 text-xs font-medium whitespace-nowrap text-ink-700 dark:text-ink-300" x-text="sel || 'Hasta'"></span>
                            <svg :class="{ 'rotate-180': open }" class="transition-transform shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 min-w-[90px]">
                            <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] py-1 max-h-48 overflow-y-auto">
                                <template x-for="o in opts" :key="o">
                                    <div class="flex items-center gap-2 px-3 py-2 text-xs cursor-pointer transition-colors text-ink-600 dark:text-ink-400 hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]"
                                        :class="{ 'text-[#5D87FF] dark:text-[#5D87FF] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': sel === o }"
                                        @click="sel = o; open = false; $wire.set('filterHoraHasta', o)">
                                        <span x-text="o"></span>
                                        <svg x-show="sel === o" class="ml-auto shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#5D87FF" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <button x-show="$wire.search || $wire.filterFecha || $wire.filterHoraDesde || $wire.filterHoraHasta || $wire.filterTurno || $wire.filterMesDesde || $wire.filterMesHasta" @click="$wire.limpiarFiltros();$nextTick(()=>$dispatch('filter-reset',{reset:1}))"
                    class="flex items-center gap-1.5 h-9 px-3 rounded-lg text-xs font-medium text-ink-500 dark:text-ink-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 border border-[#e5eaef] dark:border-white/[0.06] hover:border-red-300 dark:hover:border-red-700 transition-all shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    Limpiar filtros
                </button>
            </div>
            <button wire:click="$dispatchTo('olimpo.ocurrencias-table', 'activate-nota-form')" class="btn btn-warning btn-sm h-9 ml-auto">
                <i data-lucide="sticky-note" class="w-4 h-4 mr-1.5 shrink-0"></i>
                Nota
            </button>
            <button wire:click="nueva" class="btn btn-primary btn-sm h-9">
                <i data-lucide="plus" class="w-4 h-4 mr-1.5 shrink-0"></i>
                Nueva Ocurrencia
            </button>
        </div>
    </div>

    <livewire:olimpo.ocurrencias-table
        :search="$search"
        :filterFecha="$filterFecha"
        :filterTurno="$filterTurno"
        :filterMesDesde="$filterMesDesde"
        :filterMesHasta="$filterMesHasta"
        :filterHoraDesde="$filterHoraDesde"
        :filterHoraHasta="$filterHoraHasta"
        :refreshKey="$refreshKey"
        :wire:key="'occ-' . md5($search.$filterFecha.$filterTurno.$filterMesDesde.$filterMesHasta.$filterHoraDesde.$filterHoraHasta.$refreshKey)"
    />

    <div x-show="showPop" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg w-full max-w-lg mx-4 max-h-[80vh] overflow-y-auto"
            @click.stop
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between px-6 py-4 border-b border-ink-100 dark:border-ink-700">
                <h4 class="font-semibold text-ink-900 dark:text-ink-100 text-sm font-display" x-text="popTitle"></h4>
                <button @click="showPop = false" class="p-1 text-ink-400 hover:text-ink-700 dark:hover:text-ink-300 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="px-6 py-4 text-sm text-ink-700 dark:text-ink-300 leading-relaxed whitespace-pre-wrap" x-text="popContent"></div>
        </div>
    </div>

    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-data
        x-transition:enter="transition-all duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        wire:key="form-modal">
        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg w-full max-w-xl max-h-[90vh] overflow-y-auto mx-4"
            x-transition:enter="transition-all duration-200 ease-out"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between px-6 py-4">
                <h4 class="font-semibold text-ink-900 dark:text-ink-100 text-sm font-display">{{ $editId ? 'Editar Ocurrencia' : 'Nueva Ocurrencia' }}</h4>
                <button wire:click="cancel" class="p-1 text-ink-400 hover:text-ink-700 dark:hover:text-ink-300 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4">

                {{-- 1. Fecha / Hora Ingreso / Hora Salida — 3 columnas --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Fecha</label>
                        <input type="text" wire:model="fecha" class="input-field w-full" placeholder="dd/mm/aaaa" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Hora Ingreso</label>
                        <input type="time" wire:model="hora_ingreso" class="input-field w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Hora Salida</label>
                        <input type="time" wire:model="hora_salida" class="input-field w-full" />
                    </div>
                </div>

                {{-- 2. Persona(s) — completo --}}
                <div>
                    <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Persona(s)</label>
                    <div class="space-y-1.5">
                        @foreach($personas as $idx => $p)
                        <div wire:key="persona-{{ $idx }}" class="flex gap-1.5">
                            <div x-data="{ q: $wire.entangle('personas.{{ $idx }}').live || '', nombres: {{ Js::from($nombres) }}, aliasMap: {{ Js::from($nombresConAlias) }}, open: false }" class="relative flex-1">
                                <input type="text" x-model="q" @input="open = true" @focus="open = true"
                                    @blur="setTimeout(() => open = false, 150)"
                                    class="input-field w-full" placeholder="Escribe el nombre..." autocomplete="off" />
                                <div x-show="open && q.length > 0" x-cloak
                                    class="absolute z-20 top-full mt-1 left-0 w-full bg-white dark:bg-[#1C1F2E] rounded-lg shadow-lg border border-[#e5eaef] dark:border-white/[0.06] max-h-48 overflow-y-auto">
                                    <template x-for="n in nombres.filter(n => n.toLowerCase().includes(q.toLowerCase()))" :key="n">
                                        <div @mousedown="q = n; open = false"
                                            class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 cursor-pointer truncate">
                                            <span x-text="n"></span>
                                            <span x-show="aliasMap[n]" class="text-ink-400 text-xs" x-text="'(' + aliasMap[n] + ')'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            @if($idx === 0)
                                <button type="button" wire:click="addPersona" class="btn btn-primary btn-sm shrink-0" title="Agregar otra persona">+</button>
                            @else
                                <button type="button" wire:click="removePersona({{ $idx }})" class="btn btn-ghost btn-sm shrink-0 text-red-400 hover:text-red-600" title="Quitar persona">×</button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 3. Turno / Tipo / Otro — 3 columnas --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Turno</label>
                        <select wire:model="turno" class="input-field w-full">
                            <option value="DÍA">Día</option>
                            <option value="NOCHE">Noche</option>
                        </select>
                    </div>
                    <div>
                        <label                             class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Tipo</label>
                        <select wire:model="tipo" class="input-field w-full">
                            <option value="">Seleccionar...</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label                             class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Otro</label>
                        <input type="text" wire:model="otro" class="input-field w-full" />
                    </div>
                </div>

                {{-- 4. Vehículo / Destino / Motivo — 3 columnas --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div x-data="{ q: $wire.entangle('vehiculo').live || '', list: {{ Js::from($vehiculoList) }}, open: false }" class="relative">
                        <label                             class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Vehículo</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Vehículo..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak
                            class="absolute z-20 top-full mt-1 left-0 w-full bg-white dark:bg-[#1C1F2E] rounded-lg shadow-lg border border-[#e5eaef] dark:border-white/[0.06] max-h-48 overflow-y-auto">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                        </div>
                    </div>
                    <div x-data="{ q: $wire.entangle('destino').live || '', list: {{ Js::from($destinoList) }}, open: false }" class="relative">
                        <label                             class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Destino</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Destino..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak
                            class="absolute z-20 top-full mt-1 left-0 w-full bg-white dark:bg-[#1C1F2E] rounded-lg shadow-lg border border-[#e5eaef] dark:border-white/[0.06] max-h-48 overflow-y-auto">
                            <template x-for="d in list.filter(d => d.toLowerCase().includes(q.toLowerCase()))" :key="d">
                                <div @mousedown="q = d; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 cursor-pointer truncate"
                                    x-text="d"></div>
                            </template>
                        </div>
                    </div>
                    <div x-data="{ q: $wire.entangle('motivo').live || '', list: {{ Js::from($motivoList) }}, open: false }" class="relative">
                        <label                             class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Motivo</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Motivo..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak
                            class="absolute z-20 top-full mt-1 left-0 w-full bg-white dark:bg-[#1C1F2E] rounded-lg shadow-lg border border-[#e5eaef] dark:border-white/[0.06] max-h-48 overflow-y-auto">
                            <template x-for="m in list.filter(m => m.toLowerCase().includes(q.toLowerCase()))" :key="m">
                                <div @mousedown="q = m; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 cursor-pointer truncate"
                                    x-text="m"></div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- 5. Detalles --}}
                <div x-data="{ q: $wire.entangle('detalles').live || '', list: {{ Js::from($detallesList) }}, open: false }" class="relative">
                    <label                             class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Detalles</label>
                    <textarea x-model="q" @input="open = true" @focus="open = true"
                        @blur="setTimeout(() => open = false, 150)"
                        class="input-field w-full" rows="2" placeholder="Describe la ocurrencia..."></textarea>
                    <div x-show="open && q.length > 0" x-cloak
                        class="absolute z-20 top-full mt-1 left-0 w-full bg-white dark:bg-[#1C1F2E] rounded-lg shadow-lg border border-[#e5eaef] dark:border-white/[0.06] max-h-48 overflow-y-auto">
                        <template x-for="d in list.filter(d => d.toLowerCase().includes(q.toLowerCase()))" :key="d">
                            <div @mousedown="q = d; open = false"
                                class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 cursor-pointer truncate"
                                x-text="d"></div>
                        </template>
                    </div>
                </div>

                {{-- 6. Observación --}}
                <div x-data="{ q: $wire.entangle('observacion').live || '', list: {{ Js::from($observacionList) }}, open: false }" class="relative">
                    <label                             class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Observación</label>
                    <textarea x-model="q" @input="open = true" @focus="open = true"
                        @blur="setTimeout(() => open = false, 150)"
                        class="input-field w-full" rows="2" placeholder="Notas adicionales..."></textarea>
                    <div x-show="open && q.length > 0" x-cloak
                        class="absolute z-20 top-full mt-1 left-0 w-full bg-white dark:bg-[#1C1F2E] rounded-lg shadow-lg border border-[#e5eaef] dark:border-white/[0.06] max-h-48 overflow-y-auto">
                        <template x-for="o in list.filter(o => o.toLowerCase().includes(q.toLowerCase()))" :key="o">
                            <div @mousedown="q = o; open = false"
                                class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800 cursor-pointer truncate"
                                x-text="o"></div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-ink-100 dark:border-ink-700">
                <button wire:click="cancel" class="btn btn-secondary">Cancelar</button>
                <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="save" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span wire:loading.remove wire:target="save">{{ $editId ? 'Actualizar' : 'Guardar' }}</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <livewire:olimpo.import-modal panel="ocurrencias" wire:key="import-ocurrencias" />
</div>
