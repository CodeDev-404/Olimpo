<div x-data="{ filtersOpen: false }" x-cloak>
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
                    @click="filtersOpen = !filtersOpen"
                    :title="filtersOpen ? 'Cerrar' : 'Abrir filtros'">
                    <svg x-show="!filtersOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 6V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                    <span x-show="!filtersOpen" class="text-xs font-medium">Filtrar</span>
                    <svg x-show="filtersOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    <span x-show="filtersOpen" class="text-xs font-medium">Cerrar</span>
                </button>

                <div x-show="filtersOpen" wire:ignore x-cloak class="flex flex-nowrap items-center gap-2 sm:gap-3 filter-slide-in">
                    <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider shrink-0">Mes:</span>
                        <div class="relative" x-data="{
                            open: false,
                            y: {{ (int)$anio }},
                            m: {{ (int)$mes - 1 }},
                            val: '{{ $filterMes }}',
                            meses: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                            esActual(i){ return i === {{ (int)now()->format('m') - 1 }} && this.y === {{ (int)now()->year }}; },
                            seleccionar(i){ this.m = i; this.val = this.y + '-' + String(i + 1).padStart(2, '0'); $wire.set('filterMes', this.val); this.open = false; },
                            init(){ this.$nextTick(() => this.aplicarMes()); this.$watch('val', () => this.aplicarMes()); },
                            aplicarMes(){
                                const grid = this.$refs.mesGrid;
                                if (!grid) return;
                                grid.querySelectorAll('.mes-btn').forEach(b => {
                                    const i = this.meses.indexOf(b.textContent.trim());
                                    const s = this.y + '-' + String(i + 1).padStart(2, '0');
                                    const sel = this.val === s;
                                    const cur = this.esActual(i) && !sel;
                                    const t = (c, on) => b.classList.toggle(c, !!on);
                                    t('bg-[#5D87FF]', sel); t('text-white', sel); t('font-semibold', sel); t('shadow-sm', sel);
                                    t('bg-[#5D87FF]/20', cur); t('dark:bg-[#5D87FF]/30', cur); t('!text-[#5D87FF]', cur); t('dark:!text-[#5D87FF]', cur);
                                    t('font-bold', cur); t('ring-2', cur); t('ring-[#5D87FF]/40', cur); t('dark:ring-[#5D87FF]/40', cur);
                                    t('text-ink-600', !sel && !cur); t('dark:text-ink-300', !sel && !cur);
                                });
                            }
                        }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 px-3 h-9 rounded-lg border border-[#5D87FF]/20 dark:border-[#5D87FF]/40 bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20 text-[#5D87FF] dark:text-[#5D87FF] hover:bg-[#5D87FF]/20 dark:hover:bg-[#5D87FF]/30 cursor-pointer transition-all shrink-0">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span class="text-xs font-medium capitalize" x-text="meses[m] + ' ' + y"></span>
                            <svg :class="{ 'rotate-180': open }" class="transition-transform" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1.5" style="top:100%;left:0;width:260px">
                            <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] p-3">
                                <div class="flex items-center justify-between mb-3">
                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800 text-ink-400" @click="y--">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                                    </button>
                                    <span class="text-xs font-semibold text-ink-900 dark:text-ink-100" x-text="y"></span>
                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800 text-ink-400" @click="y++">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 gap-1.5" x-ref="mesGrid">
                                    <template x-for="(nm, i) in meses" :key="i">
                                        <button @click="seleccionar(i)"
                                            class="mes-btn h-9 rounded-lg text-xs font-medium transition-all cursor-pointer text-ink-600 dark:text-ink-300 hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]"
                                            x-text="nm"></button>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between mt-2 pt-2 border-t border-ink-100 dark:border-ink-700">
                                    <button @click="d = new Date(); y = d.getFullYear(); m = d.getMonth(); val = y + '-' + String(m + 1).padStart(2, '0'); $wire.set('filterMes', val); open = false" class="text-xs font-medium text-[#5D87FF]">Este Mes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ml-auto flex items-center gap-3">
                    <div class="flex items-center gap-3 text-[10px]">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span><span class="text-ink-500">Día</span></span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-900"></span><span class="text-ink-500">Noche</span></span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span><span class="text-ink-500">24H</span></span>
                        <span class="text-ink-300">|</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-ember-400"></span><span class="text-ink-500">Falta</span></span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span><span class="text-ink-500">Descanso</span></span>
                    </div>
                    <div class="relative" style="overflow:visible" x-data="{ exportOpen: false }" @click.outside="exportOpen = false">
                        <button @click="exportOpen = !exportOpen" class="btn btn-outline btn-sm h-9">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Exportar
                        </button>
                        <div x-show="exportOpen" x-cloak class="absolute right-0 mt-2 w-44 bg-white dark:bg-[#1C1F2E] border border-[#e5eaef] dark:border-white/[0.06] rounded-lg shadow-lg py-1 z-50">
                            <button wire:click="exportarExcel" @click="exportOpen = false" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
                                Excel (.xlsx)
                            </button>
                            <button wire:click="exportarPDF" @click="exportOpen = false" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6"/><path d="M12 12v6"/></svg>
                                PDF
                            </button>
                            <button wire:click="exportarCSV" @click="exportOpen = false" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="10" y2="13"/><line x1="14" y1="13" x2="16" y2="13"/><line x1="11" y1="13" x2="13" y2="13"/></svg>
                                CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
        @php
            $_grid = collect($this->gridData);
            $_faltas = $_grid->where('turno', 'FALTA')->count();
            $_descansos = $_grid->where('turno', 'DESCANSO')->count();
        @endphp
        <div class="card-header">
            <div class="flex items-center gap-1.5">
                <span class="text-xs text-ink-500 dark:text-ink-400 font-medium flex items-center gap-1">
                    @if($search)
                        Resultados para '{{ $search }}'
                        <span class="text-ink-300">({{ count($this->personal) }})</span>
                    @else
                        Asistencia
                        <span class="text-ink-300">({{ count($this->personal) }})</span>
                    @endif
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-400">Mes {{ $mes }}/{{ $anio }}</span>
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-400">Faltas {{ $_faltas }}</span>
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-400">Descansos {{ $_descansos }}</span>
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-600 dark:text-ink-300 font-semibold">Total {{ count($this->personal) }} personas — {{ $dias }} días</span>
                </span>
            </div>
        </div>
        <div class="card-body p-0 overflow-x-auto">
            <table class="w-full text-xs table-fixed" style="min-width: {{ 40 + $nameColumnWidth + $dias * 48 }}px">
                <thead>
                    <tr class="bg-ink-50 text-ink-600">
                        <th class="px-1 py-2.5 text-center sticky left-0 bg-ink-50 z-20 font-semibold text-[11px] uppercase tracking-wider" style="width:40px; min-width:40px">#</th>
                        <th class="px-2 py-2.5 text-left sticky left-[40px] bg-ink-50 z-10 font-semibold text-[11px] uppercase tracking-wider" style="width:{{ $nameColumnWidth }}px; min-width:{{ $nameColumnWidth }}px">Nombre</th>
                        @for($d = 1; $d <= $dias; $d++)
                            <th class="px-0 py-2.5 text-center font-semibold text-ink-500 text-[11px]" style="width:48px; min-width:48px">{{ str_pad($d,2,'0',STR_PAD_LEFT) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->personal as $idx => $p)
                        @php
                            $showHeader = !isset($prevRol) || $prevRol !== $p['grupo_rol'];
                            $prevRol = $p['grupo_rol'];
                            $headerData = [
                                'CHOFERES' => ['label' => 'CHOFERES', 'class' => 'text-ink-700 bg-ink-100'],
                                'OLIMPO' => ['label' => 'RESIDENCIA', 'class' => 'text-ink-700 bg-ink-100'],
                                'COCINA' => ['label' => 'COCINA', 'class' => 'text-amber-700 bg-amber-50'],
                                'MANTENIMIENTO' => ['label' => 'MANTENIMIENTO', 'class' => 'text-green-700 bg-green-50'],
                                'TORREÓN' => ['label' => 'TORREÓN', 'class' => 'text-purple-700 bg-purple-50'],
                            ];
                            $h = $headerData[$p['grupo_rol']] ?? ['label' => $p['grupo_rol'], 'class' => 'text-ink-700 bg-ink-100'];
                            $even = $idx % 2 === 0;
                            $bg = $even ? 'bg-white' : 'bg-ink-50/30';
                        @endphp
                        @if($showHeader)
                            <tr class="{{ $h['class'] }}">
                                <td class="px-3 py-2 font-semibold text-xs uppercase tracking-wider sticky left-0 z-20 {{ $h['class'] }}" style="width:{{ 40 + $nameColumnWidth }}px; min-width:{{ 40 + $nameColumnWidth }}px">
                                    {{ $h['label'] }}
                                </td>
                                <td colspan="{{ $dias }}"></td>
                            </tr>
                        @endif
                        <tr class="border-t border-ink-100">
                            <td class="px-1 py-1.5 text-xs text-ink-400 text-center sticky left-0 bg-white dark:bg-ink-800 z-20" style="width:40px; min-width:40px">{{ $idx + 1 }}</td>
                            <td class="px-2 py-1.5 text-sm sticky left-[40px] z-10 bg-white dark:bg-ink-800 font-medium whitespace-nowrap" style="width:{{ $nameColumnWidth }}px; min-width:{{ $nameColumnWidth }}px">
                                {{ $p['nombre'] }}
                            </td>
                            @for($d = 1; $d <= $dias; $d++)
                                @php
                                    $fecha = str_pad($d,2,'0',STR_PAD_LEFT) . '/' . $this->mes . '/' . $this->anio;
                                    $key = $p['id'] . '_' . $fecha;
                                    $reg = $this->gridData[$key] ?? null;
                                    $tieneRegistro = $reg !== null;
                                    $turno = $reg['turno'] ?? null;
                                    if ($tieneRegistro && !$turno) $turno = 'DÍA';
                                    $tieneHora = !empty($reg['hora_entrada']);
                                    if (!$tieneRegistro) {
                                        $circle = 'border-2 border-ink-300 bg-transparent';
                                        $cellBg = 'transparent';
                                        $title = 'Sin registro';
                                    } elseif ($turno === 'FALTA') {
                                        $circle = 'bg-ember-400';
                                        $cellBg = '#fefce8';
                                        $title = 'Falta';
                                    } elseif ($turno === 'DESCANSO') {
                                        $circle = 'bg-red-500';
                                        $cellBg = '#fef2f2';
                                        $title = 'Descanso';
                                    } elseif ($turno === 'NOCHE') {
                                        $circle = 'bg-blue-900';
                                        $cellBg = '#dbeafe';
                                        $title = 'Noche' . ($tieneHora ? ' ' . $reg['hora_entrada'] . ($reg['hora_salida'] ? ' → ' . $reg['hora_salida'] : '') : '');
                                    } elseif ($turno === '24H') {
                                        $circle = 'bg-purple-500';
                                        $cellBg = '#f3e8ff';
                                        $title = '24H' . ($tieneHora ? ' ' . $reg['hora_entrada'] . ($reg['hora_salida'] ? ' → ' . $reg['hora_salida'] : '') : '');
                                    } elseif ($turno === '36H') {
                                        $circle = 'bg-purple-500';
                                        $cellBg = '#f3e8ff';
                                        $title = '36H' . ($tieneHora ? ' ' . $reg['hora_entrada'] . ($reg['hora_salida'] ? ' → ' . $reg['hora_salida'] : '') : '');
                                    } elseif ($turno === 'DÍA') {
                                        $circle = 'bg-sky-400';
                                        $cellBg = '#e0f2fe';
                                        $title = 'Día' . ($tieneHora ? ' ' . $reg['hora_entrada'] . ($reg['hora_salida'] ? ' → ' . $reg['hora_salida'] : '') : '');
                                    } else {
                                        $circle = 'border-2 border-ink-300 bg-transparent';
                                        $cellBg = 'transparent';
                                        $title = 'Sin registro';
                                    }
                                @endphp
                                <td class="px-0 py-0 text-center {{ $bg }} border border-ink-50 align-middle cursor-pointer hover:brightness-95 transition-all"
                                    style="width:48px; min-width:48px; height:34px; background:{{ $cellBg }}"
                                    @dblclick.prevent="$wire.editCell({{ $p['id'] }}, {{ $d }}, '{{ $fecha }}')">
                                    <span class="inline-block w-[18px] h-[18px] rounded-full {{ $circle }}" title="{{ $title }}"></span>
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>

    <div x-data x-show="$wire.editing !== null" x-cloak
        class="modal-overlay"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="modal-card w-full max-w-sm mx-4"
            @click.stop
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="modal-header">
                <div>
                    <h4 class="font-semibold text-ink-900 text-sm">Registrar Asistencia</h4>
                    <p class="text-[11px] text-ink-400 mt-0.5">Doble clic en otra celda para cambiar</p>
                </div>
                <div class="flex items-center gap-1">
                    @if(auth()->user()?->role === 'admin' && $editing && isset($this->gridData[$editing]))
                    <button wire:click="deleteCell" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Eliminar registro">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    @endif
                    <button wire:click="cancelEdit" class="p-1 text-ink-400 hover:text-ink-700 rounded-md hover:bg-ink-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-[10px] text-ink-500 font-semibold uppercase tracking-wider mb-1.5">Tipo</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['ASISTIÓ' => 'bg-green-500', 'FALTA' => 'bg-ember-400', 'DESCANSO' => 'bg-red-500'] as $t => $dot)
                            <label class="flex items-center gap-1.5 cursor-pointer text-xs px-3 py-2 rounded-md border transition-all
                                {{ $editTipo === $t ? 'border-ink-800 font-semibold bg-ink-50' : 'border-ink-200 hover:border-ink-300 text-ink-600' }}"
                                wire:click="$set('editTipo', '{{ $t }}')">
                                <span class="inline-block w-3 h-3 rounded-full {{ $dot }}"></span>
                                <span>{{ ucfirst(strtolower($t)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($editTipo === 'ASISTIÓ')
                <div>
                    <label class="block text-[10px] text-ink-500 font-semibold uppercase tracking-wider mb-1.5">Turno</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['DÍA' => 'bg-sky-400', 'NOCHE' => 'bg-blue-900', '24H' => 'bg-purple-400'] as $t => $dot)
                            <label class="flex items-center gap-1.5 cursor-pointer text-xs px-3 py-2 rounded-md border transition-all
                                {{ $editTurno === $t ? 'border-ink-800 font-semibold bg-ink-50' : 'border-ink-200 hover:border-ink-300 text-ink-600' }}"
                                wire:click="$set('editTurno', '{{ $t }}')">
                                <span class="inline-block w-3 h-3 rounded-full {{ $dot }}"></span>
                                <span>{{ $t }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(auth()->user()?->role === 'admin' && $editTipo === 'ASISTIÓ')
                <div>
                    <label class="block text-[10px] text-ink-500 font-semibold uppercase tracking-wider mb-1.5">
                        Hora Ingreso
                        @if($editLockEntrada)<span class="text-amber-500 ml-1">(bloqueado)</span>@endif
                    </label>
                    <input type="time" wire:model.live="editValue"
                        class="input-field w-full text-sm {{ $editLockEntrada ? 'opacity-50 cursor-not-allowed' : '' }}"
                        x-init="$el.focus()"
                        {{ $editLockEntrada ? 'disabled' : '' }}>
                </div>
                <div>
                    <label class="block text-[10px] text-ink-500 font-semibold uppercase tracking-wider mb-1.5">
                        Hora Salida
                        @if($editLockSalida)<span class="text-amber-500 ml-1">(bloqueado)</span>@endif
                    </label>
                    <input type="time" wire:model.live="editValueSalida"
                        class="input-field w-full text-sm {{ $editLockSalida ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $editLockSalida ? 'disabled' : '' }}>
                </div>
                @endif

                @if($editTipo === 'ASISTIÓ' && $editValue)
                @php
                    $_tColor = match($editTurno) { 'NOCHE' => 'bg-blue-900', '24H' => 'bg-purple-400', default => 'bg-sky-400' };
                    $_tText = match($editTurno) { 'NOCHE' => 'text-blue-900', '24H' => 'text-purple-700', default => 'text-sky-700' };
                    $_tBg = match($editTurno) { 'NOCHE' => 'bg-blue-50', '24H' => 'bg-purple-50', default => 'bg-sky-50' };
                @endphp
                <div class="flex items-center justify-between bg-ink-50 rounded-lg px-3 py-2 border border-ink-200">
                    <span class="text-[10px] text-ink-500 font-semibold uppercase tracking-wider">Turno</span>
                    <span class="flex items-center gap-1.5 text-xs font-semibold {{ $_tText }} {{ $_tBg }} px-2 py-1 rounded-md">
                        <span class="inline-block w-2.5 h-2.5 rounded-full {{ $_tColor }}"></span>
                        {{ $editTurno }}
                    </span>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button wire:click="cancelEdit" class="btn btn-secondary btn-sm">Cancelar</button>
                <button wire:click="saveCell" class="btn btn-primary btn-sm">Registrar</button>
            </div>
        </div>
    </div>

</div>