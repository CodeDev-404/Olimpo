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
@endphp

<div class="card">
    <div class="card-header">
        <div class="flex items-center gap-1.5">
            <span class="text-xs text-ink-500 dark:text-ink-400 font-medium flex items-center gap-1">
                @if($search)
                    Resultados para '{{ $search }}'
                    <span class="text-ink-300">({{ $ocurrencias instanceof \Illuminate\Pagination\LengthAwarePaginator ? $ocurrencias->total() : count($ocurrencias) }})</span>
                @else
                    Ocurrencias
                    <span class="text-ink-300">({{ $ocurrencias instanceof \Illuminate\Pagination\LengthAwarePaginator ? $ocurrencias->total() : count($ocurrencias) }})</span>
                @endif
                <span class="text-ink-300 mx-0.5">·</span>
                <span class="text-ink-400">Sem {{ $weekCount }}</span>
                <span class="text-ink-300 mx-0.5">·</span>
                <span class="text-ink-400">Mes {{ $monthCount }}</span>
                <span class="text-ink-300 mx-0.5">·</span>
                <span class="text-ink-400">Año {{ $yearCount }}</span>
                <span class="text-ink-300 mx-0.5">·</span>
                <span class="text-ink-600 dark:text-ink-300 font-semibold">Total {{ $totalCount }}</span>
            </span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($selectMode)
                <span class="text-xs text-ink-400 font-medium whitespace-nowrap">{{ count($selectedIds) }} seleccionados</span>
            @endif

            <button wire:click="toggleSelectMode" style="width:130px" class="btn btn-sm {{ $selectMode ? 'btn-warning' : 'btn-outline' }}">
                @if($selectMode)
                    <i data-lucide="circle-x" class="w-4 h-4 mr-1.5 shrink-0"></i>
                    Cancelar
                @else
                    <i data-lucide="check-square" class="w-4 h-4 mr-1.5 shrink-0"></i>
                    Seleccionar
                @endif
            </button>

            <button wire:click="$dispatch('openImportModal')" class="btn btn-outline btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span class="ml-1.5">Importar</span>
            </button>

            <div class="relative" style="overflow:visible" x-data="{ exportOpen: false }" @click.outside="exportOpen = false">
                <button @click="exportOpen = !exportOpen" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span class="ml-1.5">Exportar</span>
                </button>
                <div x-show="exportOpen" x-cloak class="absolute right-0 mt-2 w-44 bg-white dark:bg-[#1C1F2E] border border-[#e5eaef] dark:border-white/[0.06] rounded-lg shadow-lg py-1 z-20">
                    <button wire:click="$dispatch('exportarExcel')" @click="exportOpen = false" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
                        Excel (.xlsx)
                    </button>
                    <button wire:click="$dispatch('exportarPDF')" @click="exportOpen = false" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15h6"/><path d="M12 12v6"/></svg>
                        PDF
                    </button>
                    <button wire:click="$dispatch('exportarCSV')" @click="exportOpen = false" class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="10" y2="13"/><line x1="14" y1="13" x2="16" y2="13"/><line x1="11" y1="13" x2="13" y2="13"/></svg>
                        CSV
                    </button>
                </div>
            </div>

            <button wire:click="nuevaNota" class="btn btn-outline btn-sm">
                <i data-lucide="sticky-note" class="w-4 h-4 mr-1.5 shrink-0"></i>
                Nota
            </button>

        </div>
    </div>
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table-adminlte">
                <thead>
                    <tr>
                        @if($selectMode)
                        <th class="w-10">
                            <input type="checkbox" wire:click="toggleSelectAll"
                                {{ count($selectedIds) > 0 && collect($ocurrencias->items())->pluck('id')->every(fn($id) => in_array($id, $selectedIds)) ? 'checked' : '' }}>
                        </th>
                        @endif
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Ingreso</th>
                        <th>Salida</th>
                        <th>Nombre</th>
                        <th>Vehículo</th>
                        <th>Destino</th>
                        <th>Motivo</th>
                        <th>Detalles</th>
                        <th>Obs.</th>
                        <th>Cargo</th>
                        <th>Tipo</th>
                        <th>Otro</th>
                        <th>Turno</th>
                        <th>Usuario</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ocurrencias as $i => $oc)
                        @php $isNota = $oc['es_nota'] ?? false; @endphp
                        <tr class="whitespace-nowrap {{ $selectMode && in_array($oc['id'], $selectedIds) ? 'bg-ink-50 dark:bg-ink-800' : '' }} @if($isNota) @php $t = $oc['tipo'] ?? 'Nota'; $par = $i % 2 === 0; @endphp {{ $t === 'Importante' ? ($par ? 'bg-amber-100/70 dark:bg-amber-900/25' : 'bg-amber-200/70 dark:bg-amber-900/40') : ($par ? 'bg-emerald-100/70 dark:bg-emerald-900/20' : 'bg-emerald-200/60 dark:bg-emerald-900/30') }} @endif">
                            @if($selectMode)
                            <td class="w-10" wire:click.stop="toggleSelect({{ $oc['id'] }})">
                                <input type="checkbox" {{ in_array($oc['id'], $selectedIds) ? 'checked' : '' }}>
                            </td>
                            @endif
                            <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>

                            @if($isNota)
                                @php
                                    $notaHasData = !empty($oc['hora_ingreso']) || !empty($oc['hora_salida']) || !empty($oc['persona_nombre']);
                                @endphp

                                <td class="font-medium">{!! h($oc['fecha'] ?? '', $search) !!}</td>

                                @if($notaHasData)
                                    <td class="tabular-nums">{{ $oc['hora_ingreso'] ?? '—' }}</td>
                                    <td class="tabular-nums">{{ $oc['hora_salida'] ?? '—' }}</td>
                                    <td class="max-w-[140px] truncate font-medium">{!! h($oc['persona_nombre'] ?? '', $search) !!}</td>
                                    <td colspan="6" class="text-ink-500 italic whitespace-normal max-w-none">
                                        {!! h($oc['nota_texto'] ?? '', $search) !!}
                                    </td>
                                @else
                                    <td colspan="9" class="text-ink-500 italic whitespace-normal max-w-none">
                                        {!! h($oc['nota_texto'] ?? '', $search) !!}
                                    </td>
                                @endif

                                <td>
                                    @if($oc['tipo'] ?? false)
                                    <span class="badge" style="background: {{ $this->tipoColor($oc['tipo']) }}18; color: {{ $this->tipoColor($oc['tipo']) }}">
                                        {!! h($oc['tipo'], $search) !!}
                                    </span>
                                    @else
                                    <span class="text-ink-300">—</span>
                                    @endif
                                </td>
                                <td class="max-w-[100px] truncate text-ink-400 cursor-pointer"
                                    @dblclick="popContent = $el.dataset.content; popTitle = 'Otro'; showPop = true"
                                    data-content="{{ $oc['otro'] ?? '' }}">@if(!empty($oc['otro'])){!! h($oc['otro'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                                <td><span class="badge {{ $oc['turno'] === 'NOCHE' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-ember-100 text-ember-700 dark:bg-ember-900/30 dark:text-ember-300' }}">{{ $oc['turno'] ?? 'DÍA' }}</span></td>
                                <td class="text-ink-500 dark:text-ink-400 text-xs whitespace-normal">
                                    @if(!empty($oc['usuario_nombre']))
                                        <span>{{ $oc['usuario_nombre'] }}</span>
                                        @if($oc['created_at'] != $oc['updated_at'])
                                            <i data-lucide="pencil" class="w-3 h-3 inline-block ml-1 text-amber-500" title="Modificado"></i>
                                        @endif
                                    @else
                                        <span class="text-ink-300">—</span>
                                    @endif
                                </td>
                            @else
                            <td class="font-medium">@if(!empty($oc['fecha'])){{ $oc['fecha'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="tabular-nums">@if(!empty($oc['hora_ingreso'])){{ $oc['hora_ingreso'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="tabular-nums">@if(!empty($oc['hora_salida'])){{ $oc['hora_salida'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="max-w-[140px] truncate font-medium cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Nombre'; showPop = true"
                                data-content="{{ $oc['persona_nombre'] ?? '' }}{{ !empty($oc['persona_alias']) ? ' (' . $oc['persona_alias'] . ')' : '' }}">
                                @if(!empty($oc['persona_alias']))
                                    {!! h($oc['persona_alias'], $search) !!}
                                @elseif(!empty($oc['persona_nombre']))
                                    {!! h($oc['persona_nombre'], $search) !!}
                                @else
                                    <span class="text-ink-300">—</span>
                                @endif
                            </td>
                            <td class="max-w-[100px] truncate text-ink-500 dark:text-ink-400 cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Vehículo'; showPop = true"
                                data-content="{{ $oc['vehiculo'] ?? '' }}">@if(!empty($oc['vehiculo'])){!! h($oc['vehiculo'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="max-w-[100px] truncate text-ink-500 dark:text-ink-400 cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Destino'; showPop = true"
                                data-content="{{ $oc['destino'] ?? '' }}">@if(!empty($oc['destino'])){!! h($oc['destino'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="max-w-[100px] truncate text-ink-500 dark:text-ink-400 cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Motivo'; showPop = true"
                                data-content="{{ $oc['motivo'] ?? '' }}">@if(!empty($oc['motivo'])){!! h($oc['motivo'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="max-w-[180px] truncate text-ink-500 dark:text-ink-400 cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Detalles'; showPop = true"
                                data-content="{{ $oc['detalles'] ?? '' }}">@if(!empty($oc['detalles'])){!! h($oc['detalles'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="max-w-[120px] truncate text-ink-400 italic cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Observación'; showPop = true"
                                data-content="{{ $oc['observacion'] ?? '' }}">@if(!empty($oc['observacion'])){!! h($oc['observacion'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="max-w-[100px] truncate text-ink-600 dark:text-ink-400 cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Cargo'; showPop = true"
                                data-content="{{ $oc['persona_cargo'] ?? '' }}">@if(!empty($oc['persona_cargo'])){!! h($oc['persona_cargo'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                            <td>
                                @if($oc['tipo'] ?? false)
                                <span class="badge" style="background: {{ $this->tipoColor($oc['tipo']) }}18; color: {{ $this->tipoColor($oc['tipo']) }}">
                                    {!! h($oc['tipo'], $search) !!}
                                </span>
                                @else
                                <span class="text-ink-300">—</span>
                                @endif
                            </td>
                            <td class="max-w-[100px] truncate text-ink-400 cursor-pointer"
                                @dblclick="popContent = $el.dataset.content; popTitle = 'Otro'; showPop = true"
                                data-content="{{ $oc['otro'] ?? '' }}">@if(!empty($oc['otro'])){!! h($oc['otro'], $search) !!}@else<span class="text-ink-300">—</span>@endif</td>
                            <td><span class="badge {{ $oc['turno'] === 'NOCHE' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-ember-100 text-ember-700 dark:bg-ember-900/30 dark:text-ember-300' }}">{{ $oc['turno'] ?? 'DÍA' }}</span></td>
                            <td class="text-ink-500 dark:text-ink-400 text-xs whitespace-normal">
                                @if(!empty($oc['usuario_nombre']))
                                    <span>{{ $oc['usuario_nombre'] }}</span>
                                    @if($oc['created_at'] != $oc['updated_at'])
                                        <i data-lucide="pencil" class="w-3 h-3 inline-block ml-1 text-amber-500" title="Modificado"></i>
                                    @endif
                                @else
                                    <span class="text-ink-300">—</span>
                                @endif
                            </td>
                            @endif

                            <td class="w-10" x-data="{ open: false, top: 0, left: 0 }" @click.outside="open = false">
                                <button @click="open = !open; if(open) { let r = $el.getBoundingClientRect(); top = r.bottom + 4; left = r.right - 176; } $event.stopPropagation()" class="p-1.5 rounded-md hover:bg-ink-100 dark:hover:bg-white/[0.06] text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">
                                    <i data-lucide="ellipsis-vertical" class="w-4 h-4"></i>
                                </button>
                                <div x-show="open" x-cloak @click.stop
                                    :style="'position:fixed;top:' + top + 'px;left:' + left + 'px;z-index:9999'"
                                    class="w-44 bg-white dark:bg-[#1C1F2E] border border-[#e5eaef] dark:border-white/[0.06] rounded-lg shadow-lg py-1">
                                    <button wire:click="$dispatch('editar', { id: {{ $oc['id'] }} })" @click="open = false"
                                        class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                                        <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                        Editar
                                    </button>
                                    <button wire:click="$dispatch('duplicar', { id: {{ $oc['id'] }} })" @click="open = false"
                                        class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                                        <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0"></i>
                                        Duplicar
                                    </button>
                                    <button wire:click="$dispatch('eliminar', { id: {{ $oc['id'] }} })" wire:confirm="¿Eliminar esta ocurrencia?" @click="open = false"
                                        class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <i data-lucide="trash-2" class="w-4 h-4 shrink-0"></i>
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $selectMode ? 17 : 16 }}" class="px-3 py-16 text-center">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i data-lucide="inbox" class="w-8 h-8 text-ink-300 dark:text-white/20"></i>
                                    </div>
                                    <p class="empty-state-title">No hay ocurrencias registradas</p>
                                    <p class="empty-state-desc">Las ocurrencias aparecerán aquí una vez que se registren.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($ocurrencias instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="card-footer">
        {{ $ocurrencias->links('livewire.olimpo.pagination-links') }}
    </div>
    @endif
    @if($showNotaForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 transform" wire:click.self="cancelarNota">
    <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg border border-[#e5eaef] dark:border-white/[0.06] w-full max-w-lg mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-[#e5eaef] dark:border-white/[0.06]">
            <h3 class="text-sm font-semibold text-ink-800 dark:text-white flex items-center gap-2">
                <i data-lucide="sticky-note" class="w-4 h-4"></i>
                Nueva Nota
            </h3>
            <button wire:click="cancelarNota" class="p-1 rounded-md hover:bg-ink-100 dark:hover:bg-white/[0.06] text-ink-400 hover:text-ink-600 dark:text-white/60">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-5 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1">Fecha</label>
                    <input type="text" wire:model="notaFecha" class="input input-sm w-full" placeholder="dd/mm/aaaa">
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1">Turno</label>
                    <select wire:model="notaTurno" class="input input-sm w-full">
                        <option value="DÍA">DÍA</option>
                        <option value="NOCHE">NOCHE</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1">Hora Ingreso</label>
                    <input type="time" wire:model="notaHoraIngreso" class="input input-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1">Hora Salida</label>
                    <input type="time" wire:model="notaHoraSalida" class="input input-sm w-full">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1">Nombre</label>
                    <input type="text" wire:model="notaNombre" class="input input-sm w-full" placeholder="Nombre del personal">
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1">Tipo</label>
                    <select wire:model="notaTipo" class="input input-sm w-full">
                        <option value="Nota">Nota</option>
                        <option value="Importante">Importante</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-ink-500 dark:text-ink-400 mb-1">Nota <span class="text-red-500">*</span></label>
                <textarea wire:model="nota_texto" rows="4" class="input input-sm w-full resize-none" placeholder="Contenido de la nota..."></textarea>
                @error('nota_texto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-ink-200 dark:border-ink-700">
            <button wire:click="cancelarNota" class="btn btn-sm btn-outline">Cancelar</button>
            <button wire:click="guardarNota" class="btn btn-sm btn-primary">
                <i data-lucide="save" class="w-4 h-4 mr-1.5"></i>
                Guardar Nota
            </button>
        </div>
    </div>
</div>
@endif
</div>
