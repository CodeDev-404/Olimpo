<div>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live="search" placeholder="Buscar..."
                    class="input-field pl-9 w-full sm:w-56" />
            </div>
            <input type="date" wire:model.live="filterFecha" class="input-field w-full sm:w-auto" title="Filtrar por fecha" />
            <select wire:model.live="filterTurno" class="input-field w-full sm:w-auto">
                <option value="">Todos los turnos</option>
                <option value="DÍA">Día</option>
                <option value="NOCHE">Noche</option>
            </select>
            @if($search || $filterFecha || $filterHoraDesde || $filterHoraHasta || $filterTurno)
                <button wire:click="limpiarFiltros" class="btn btn-ghost btn-sm">Limpiar</button>
            @endif
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button wire:click="nueva" class="btn btn-primary btn-sm w-full sm:w-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nueva Ocurrencia
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="text-xs text-ink-500 font-medium">
                {{ $search ? "Resultados para '{$search}'" : 'Ocurrencias' }}
                <span class="text-ink-300">({{ $ocurrencias instanceof \Illuminate\Pagination\LengthAwarePaginator ? $ocurrencias->total() : count($ocurrencias) }})</span>
            </span>
            @if($selectedId)
            <div class="flex items-center gap-1">
                <button wire:click="editar" class="btn btn-ghost btn-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                    </svg>
                    Editar
                </button>
                <button wire:click="duplicar" class="btn btn-ghost btn-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/>
                    </svg>
                    Duplicar
                </button>
                <button wire:click="eliminar" wire:confirm="¿Eliminar esta ocurrencia?" class="btn btn-ghost btn-xs text-red-500 hover:bg-red-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14l-1.5-7.5a2.25 2.25 0 00-2.25-2.25h-3.5m-3.5 0a2.25 2.25 0 00-2.25 2.25L4.5 14m7.5-7.5V2.25m0 0h-1.29m1.29 0h1.29M6 14l1.5 7.5A2.25 2.25 0 009.75 23.5h4.5a2.25 2.25 0 002.25-2.25L18 14M6 14h12"/>
                    </svg>
                </button>
            </div>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Ingreso</th>
                            <th>Salida</th>
                            <th>Nombre</th>
                            <th>Detalles</th>
                            <th>Obs.</th>
                            <th>Cargo</th>
                            <th>Tipo</th>
                            <th>Otro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ocurrencias as $i => $oc)
                            <tr wire:click="selectOcurrencia({{ $oc['id'] }})"
                                class="cursor-pointer {{ $selectedId === $oc['id'] ? 'bg-ink-50' : '' }}">
                                <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                                <td class="font-medium">{{ $oc['fecha'] }}</td>
                                <td class="tabular-nums">{{ $oc['hora_ingreso'] }}</td>
                                <td class="tabular-nums">{{ $oc['hora_salida'] }}</td>
                                <td class="font-medium">{{ $oc['persona_nombre'] }}</td>
                                <td class="max-w-[180px] truncate text-ink-500" title="{{ $oc['detalles'] }}">{{ $oc['detalles'] }}</td>
                                <td class="max-w-[120px] truncate text-ink-400 italic" title="{{ $oc['observacion'] ?? '' }}">{{ $oc['observacion'] ?? '—' }}</td>
                                <td class="text-ink-600">{{ $oc['persona_cargo'] ?? '—' }}</td>
                                <td>
                                    <span class="badge" style="background: {{ $this->tipoColor($oc['tipo']) }}18; color: {{ $this->tipoColor($oc['tipo']) }}">
                                        {{ $oc['tipo'] }}
                                    </span>
                                </td>
                                <td class="text-ink-400">{{ $oc['otro'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-12 text-center text-ink-400">
                                    No hay ocurrencias registradas
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
    </div>

    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape.window="$wire.cancel"
        x-data
        wire:key="form-modal">
        <div class="bg-white rounded-xl shadow-[0_8px_32px_rgb(0_0_0_/_0.12)] w-full max-w-xl max-h-[90vh] overflow-y-auto mx-4">
            <div class="flex items-center justify-between px-6 py-4">
                <h4 class="font-semibold text-ink-900 text-sm">{{ $editId ? 'Editar Ocurrencia' : 'Nueva Ocurrencia' }}</h4>
                <button wire:click="cancel" class="p-1 text-ink-400 hover:text-ink-700 rounded-md hover:bg-ink-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Persona</label>
                    <input type="text" wire:model="persona_nombre" list="nombres-list" class="input-field w-full" placeholder="Escribe el nombre..." autocomplete="off" />
                    <datalist id="nombres-list">
                        @foreach($nombres as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </datalist>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Fecha</label>
                        <input type="text" wire:model="fecha" class="input-field w-full" placeholder="dd/mm/aaaa" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Tipo</label>
                        <select wire:model="tipo" class="input-field w-full">
                            <option value="">Seleccionar...</option>
                            @foreach($tipos as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Hora Ingreso</label>
                        <input type="time" wire:model="hora_ingreso" class="input-field w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Hora Salida</label>
                        <input type="time" wire:model="hora_salida" class="input-field w-full" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Turno</label>
                        <select wire:model="turno" class="input-field w-full">
                            <option value="DÍA">Día</option>
                            <option value="NOCHE">Noche</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Otro</label>
                        <input type="text" wire:model="otro" class="input-field w-full" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Detalles</label>
                    <textarea wire:model="detalles" class="input-field w-full" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Observación</label>
                    <textarea wire:model="observacion" class="input-field w-full" rows="2"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-ink-100">
                <button wire:click="cancel" class="btn btn-secondary">Cancelar</button>
                <button wire:click="save" class="btn btn-primary">
                    {{ $editId ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
