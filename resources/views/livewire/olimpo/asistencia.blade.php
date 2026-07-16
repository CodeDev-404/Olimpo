<div>
    <div class="card mb-5">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider">Mes:</span>
                <input type="month" wire:model.live="filterMes" class="input-field">
                <button wire:click="guardarMes" class="btn btn-success">Guardar Mes</button>
                <button wire:click="$dispatch('openImportModal')" class="btn btn-outline text-blue-600 border-blue-300 hover:bg-blue-50">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Importar
                </button>
                <span class="w-px h-5 bg-ink-200"></span>
                <div class="flex items-center gap-3 text-[10px]">
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span><span class="text-ink-500">Día</span></span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-900"></span><span class="text-ink-500">Noche</span></span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span><span class="text-ink-500">24H</span></span>
                    <span class="text-ink-300">|</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-ember-400"></span><span class="text-ink-500">Falta</span></span>
                    <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span><span class="text-ink-500">Descanso</span></span>
                </div>
                <span class="text-xs text-ink-400 ml-auto">{{ count($personal) }} personas — {{ $dias }} días</span>
            </div>
        </div>
    </div>

    <div class="card overflow-x-auto">
        <div class="card-body p-0" style="min-width: {{ $nameColumnWidth + $dias * 48 }}px">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-ink-50 text-ink-600">
                        <th class="px-2 py-2.5 text-left sticky left-0 bg-ink-50 z-10 font-semibold text-[11px] uppercase tracking-wider" style="width:{{ $nameColumnWidth }}px; min-width:{{ $nameColumnWidth }}px">Nombre</th>
                        @for($d = 1; $d <= $dias; $d++)
                            <th class="px-0 py-2.5 text-center font-semibold text-ink-500 text-[11px]" style="width:48px; min-width:48px">{{ str_pad($d,2,'0',STR_PAD_LEFT) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($personal as $idx => $p)
                        @php
                            $showHeader = !isset($prevRol) || $prevRol !== $p['grupo_rol'];
                            $prevRol = $p['grupo_rol'];
                            $headerData = [
                                'CHOFERES' => ['label' => 'CHOFERES', 'class' => 'text-ink-700 bg-ink-100'],
                                'OLIMPO' => ['label' => 'OLIMPO', 'class' => 'text-ink-700 bg-ink-100'],
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
                                <td colspan="{{ 1 + $dias }}" class="px-3 py-2 font-semibold text-xs uppercase tracking-wider">
                                    {{ $h['label'] }}
                                </td>
                            </tr>
                        @endif
                        <tr class="border-t border-ink-100">
                            <td class="px-2 py-1.5 text-sm sticky left-0 {{ $bg }} font-medium whitespace-nowrap" style="width:{{ $nameColumnWidth }}px; min-width:{{ $nameColumnWidth }}px">
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

    <div x-data x-show="$wire.editing !== null" x-cloak
        class="modal-overlay"
        @click.self="$wire.cancelEdit()"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="modal-card w-80 max-w-sm"
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
                    @if(auth()->user()?->role === 'admin' && $editing && isset($gridData[$editing]))
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

    <livewire:olimpo.import-modal panel="asistencia" wire:key="import-asistencia" />
</div>
