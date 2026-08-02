<div x-data="{ filtersOpen: false }" x-cloak>
    <div class="card mb-5" style="overflow:visible">
        <div class="card-body">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-[12.8rem] sm:min-w-[12.8rem]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live="search" placeholder="Buscar persona..."
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

            <div x-show="!filtersOpen && {{ ($this->search !== '' || $this->filterEstado !== '') ? 'true' : 'false' }}" x-cloak>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20 text-[#5D87FF] dark:text-[#5D87FF] border border-[#5D87FF]/20 dark:border-[#5D87FF]/40">
                    ● {{ ($this->search !== '' ? 1 : 0) + ($this->filterEstado !== '' ? 1 : 0) }}
                </span>
            </div>

            <div x-show="filtersOpen" wire:ignore x-cloak class="flex flex-nowrap items-center gap-2 sm:gap-3 filter-slide-in overflow-x-auto pb-80 -mb-80">
                <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider shrink-0">Estado:</span>
                <div x-data="{ open: false, sel: '{{ $filterEstado ?: 'Todos' }}', vals: ['Todos','ACTIVO','INACTIVO'], labels: ['Todos','Activos','Inactivos'] }"
                     @filter-reset.window="if($event.detail.reset){sel='Todos'}else{if(sel!==($wire.filterEstado||'Todos'))sel=$wire.filterEstado||'Todos'}"
                     class="relative">
                    <div class="input-field flex items-center gap-2 cursor-pointer h-9 !px-[10px] min-w-[100px]" @click="open = !open">
                        <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: sel==='ACTIVO' ? '#22c55e' : sel==='INACTIVO' ? '#ef4444' : 'rgba(161,161,170,0.3)' }"></span>
                        <span class="flex-1 text-xs font-medium whitespace-nowrap text-ink-700 dark:text-ink-300" x-text="labels[vals.indexOf(sel)]"></span>
                        <svg :class="{ 'rotate-180': open }" class="transition-transform shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute z-50 mt-1 min-w-[120px]">
                        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg dark:shadow-none border border-[#e5eaef] dark:border-white/[0.06] py-1">
                            <template x-for="(o, i) in vals" :key="o">
                                <div class="flex items-center gap-2 px-3 py-2 text-xs cursor-pointer transition-colors text-ink-600 dark:text-ink-400 hover:bg-[#5D87FF]/10 dark:hover:bg-[#5D87FF]/20 hover:text-[#5D87FF] dark:hover:text-[#5D87FF]"
                                    :class="{ 'text-[#5D87FF] dark:text-[#5D87FF] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': sel === o }"
                                    @click="sel = o; open = false; $wire.set('filterEstado', o === 'Todos' ? '' : o)">
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: o==='ACTIVO' ? '#22c55e' : o==='INACTIVO' ? '#ef4444' : 'rgba(161,161,170,0.3)' }"></span>
                                    <span x-text="labels[i]"></span>
                                    <svg x-show="sel === o" class="ml-auto shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#5D87FF" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <button x-show="$wire.search || $wire.filterEstado" @click="$wire.limpiarFiltros();$nextTick(()=>$dispatch('filter-reset',{reset:1}))"
                    class="flex items-center gap-1.5 h-9 px-3 rounded-lg text-xs font-medium text-ink-500 dark:text-ink-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 border border-[#e5eaef] dark:border-white/[0.06] hover:border-red-300 dark:hover:border-red-700 transition-all shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    Limpiar filtros
                </button>
            </div>

            <button wire:click="nueva" class="btn btn-primary btn-sm h-9 ml-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo Personal
            </button>
        </div>
        </div>
        <div class="card">
            <div class="card-header">
            <div class="flex items-center gap-1.5">
            <span class="text-xs text-ink-500 dark:text-ink-400 font-medium flex items-center gap-1">
                @if($search)
                    Resultados para '{{ $search }}'
                    <span class="text-ink-300">({{ count($this->personal) }})</span>
                @else
                    Todo el personal
                    <span class="text-ink-300">({{ count($this->personal) }})</span>
                @endif
                <span class="text-ink-300 mx-0.5">·</span>
                <span class="text-ink-400">Activos {{ collect($this->personal)->where('estado', 'ACTIVO')->count() }}</span>
                <span class="text-ink-300 mx-0.5">·</span>
                <span class="text-ink-400">Inactivos {{ collect($this->personal)->where('estado', 'INACTIVO')->count() }}</span>
                <span class="text-ink-300 mx-0.5">·</span>
                <span class="text-ink-600 dark:text-ink-300 font-semibold">Total {{ count($this->personal) }}</span>
            </span>
        </div>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            <th class="hidden sm:table-cell">#</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th class="hidden md:table-cell">Área</th>
                            <th class="hidden lg:table-cell">Cumpleaños</th>
                            <th class="hidden lg:table-cell">Celular</th>
                            <th class="hidden md:table-cell">DNI</th>
                            <th class="hidden md:table-cell">Edad</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->personal as $i => $p)
                            <tr wire:click="selectPersona({{ $p['id'] }})"
                                class="cursor-pointer {{ $selectedId === $p['id'] ? 'bg-ink-50' : ($i % 2 === 1 ? 'table-row-zebra' : '') }}">
                                <td class="hidden sm:table-cell font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                                <td class="font-medium">
                                    @if(!empty($p['nombre'])){{ $p['nombre'] }}@else<span class="text-ink-300">—</span>@endif
                                    @if(!empty($p['alias']))
                                        <span class="text-ink-400 text-xs ml-1">({{ $p['alias'] }})</span>
                                    @endif
                                </td>
                                <td class="text-ink-600">@if(!empty($p['cargo'])){{ $p['cargo'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="hidden md:table-cell text-ink-500">@if(!empty($p['departamento'])){{ $p['departamento'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="hidden lg:table-cell tabular-nums text-ink-500">@if(!empty($p['cumpleaños_format'])){{ $p['cumpleaños_format'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="hidden lg:table-cell tabular-nums">@if(!empty($p['telefono'])){{ $p['telefono'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="hidden md:table-cell tabular-nums text-ink-500">@if(!empty($p['documento'])){{ $p['documento'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="hidden md:table-cell text-ink-500">@if(!empty($p['edad'])){{ $p['edad'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td>
                                    @if(($p['estado'] ?? 'ACTIVO') === 'ACTIVO')
                                        <span class="badge bg-green-100 text-green-700">Activo</span>
                                    @else
                                        <span class="badge bg-red-100 text-red-700">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <button wire:click="editar" class="btn btn-ghost btn-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                            </svg>
                                        </button>
                                        <button wire:click="baja" wire:confirm="¿Dar de baja a esta persona?" class="btn btn-ghost btn-xs text-ember-600 hover:bg-ember-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </button>
                                        <button wire:click="eliminar" wire:confirm="¿Eliminar permanentemente?" class="btn btn-ghost btn-xs text-red-500 hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14l-1.5-7.5a2.25 2.25 0 00-2.25-2.25h-3.5m-3.5 0a2.25 2.25 0 00-2.25 2.25L4.5 14m7.5-7.5V2.25m0 0h-1.29m1.29 0h1.29M6 14l1.5 7.5A2.25 2.25 0 009.75 23.5h4.5a2.25 2.25 0 002.25-2.25L18 14M6 14h12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-3 py-12 text-center text-ink-400">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i data-lucide="users" class="w-8 h-8 text-ink-300 dark:text-white/20"></i>
                                        </div>
                                        <p class="empty-state-title">No hay personal registrado</p>
                                        <p class="empty-state-desc">El personal aparecerá aquí una vez que se registre.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>
    </div>

    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-data
        x-transition:enter="transition-all duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        wire:key="form-modal">
        <div class="bg-white rounded-xl shadow-[0_8px_32px_rgb(0_0_0_/_0.12)] w-full max-w-lg max-h-[90vh] overflow-y-auto mx-4"
            x-transition:enter="transition-all duration-200 ease-out"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between px-6 py-4">
                <h4 class="font-semibold text-ink-900 text-sm">{{ $editId ? 'Editar Personal' : 'Nuevo Personal' }}</h4>
                <button wire:click="cancel" class="p-1 text-ink-400 hover:text-ink-700 rounded-md hover:bg-ink-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Alias</label>
                        <input type="text" wire:model="alias" class="input-field w-full" placeholder="Apodo o alias" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Documento</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="documento" class="input-field flex-1" maxlength="8" />
                            <button wire:click="consultarDni" class="btn btn-outline btn-sm shrink-0" type="button">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="text-[11px] text-ink-400 font-medium">Búsqueda:</span>
                            <select wire:model="proveedor" class="input-field text-xs px-2 py-1 w-24">
                                @foreach($proveedores as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Nombre</label>
                        <input type="text" wire:model="nombre" class="input-field w-full" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Segundo nombre</label>
                        <input type="text" wire:model="segundoNombre" class="input-field w-full" />
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Ap. Paterno</label>
                        <input type="text" wire:model="apellidoPaterno" class="input-field w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Ap. Materno</label>
                        <input type="text" wire:model="apellidoMaterno" class="input-field w-full" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Cargo</label>
                        <select wire:model="cargoId" class="input-field w-full">
                            <option value="">Seleccionar...</option>
                            @foreach($cargos as $c)
                                <option value="{{ $c['id'] }}">{{ $c['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Departamento</label>
                        <input type="text" wire:model="departamento" class="input-field w-full" />
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Teléfono</label>
                        <input type="text" wire:model="telefono" class="input-field w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Fecha Nacimiento</label>
                        <input type="date" wire:model="fechaNacimiento" class="input-field w-full" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" wire:model="email" class="input-field w-full" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Estado</label>
                    <select wire:model="estado" class="input-field w-full">
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-[#e5eaef]">
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
</div>
