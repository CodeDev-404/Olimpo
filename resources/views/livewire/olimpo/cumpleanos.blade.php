<div x-data="cumpleanosComponent(@entangle('cumpleanosHoy'))" x-init="init()" x-cloak>
    <nav class="mb-4 flex items-center gap-1.5 text-xs text-ink-400 dark:text-ink-500">
        <a href="{{ route('olimpo.recordatorios') }}" wire:navigate class="hover:text-ink-600 dark:hover:text-ink-300 transition-colors">Recordatorios</a>
        <span class="text-ink-300">/</span>
        <span class="font-medium text-ink-600 dark:text-ink-300">Cumpleaños</span>
    </nav>

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

            <div x-show="!filtersOpen && {{ $activeFilters > 0 ? 'true' : 'false' }}" x-cloak>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20 text-[#5D87FF] dark:text-[#5D87FF] border border-[#5D87FF]/20 dark:border-[#5D87FF]/40">
                    ● {{ $activeFilters }}
                </span>
            </div>

            <div x-show="filtersOpen" wire:ignore x-cloak class="flex flex-nowrap items-center gap-2 sm:gap-3 filter-slide-in overflow-x-auto pb-80 -mb-80">
                <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider shrink-0">Recordatorio:</span>
                <select wire:model.live="filterRecordatorio" class="input-field h-9 w-36 shrink-0">
                    <option value="">Todos</option>
                    <option value="activos">Con recordatorio</option>
                    <option value="inactivos">Sin recordatorio</option>
                </select>

                <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider shrink-0">Próximos:</span>
                <select wire:model.live="filterProximidad" class="input-field h-9 w-28 shrink-0">
                    <option value="">Todos</option>
                    <option value="7">≤ 7 días</option>
                    <option value="30">≤ 30 días</option>
                    <option value="60">≤ 60 días</option>
                </select>

                <button x-show="$wire.search || $wire.filterProximidad || $wire.filterRecordatorio" @click="$wire.limpiarFiltros()"
                    class="flex items-center gap-1.5 h-9 px-3 rounded-lg text-xs font-medium text-ink-500 dark:text-ink-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 border border-[#e5eaef] dark:border-white/[0.06] hover:border-red-300 dark:hover:border-red-700 transition-all shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    Limpiar filtros
                </button>
            </div>

            <button @click="testNotification()" class="btn btn-info btn-sm ml-auto">
                <i data-lucide="bell" class="w-4 h-4 mr-1"></i>
                Probar Notificación
            </button>

            <button wire:click="nuevo" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Agregar
            </button>
        </div>

    @if($showForm)
    <div class="modal-overlay"
        x-data x-show="$wire.showForm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="modal-card max-w-lg"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="modal-header">
                <h3 class="text-base font-semibold text-ink-900">{{ $editId ? 'Editar Cumpleaños' : 'Nuevo Cumpleaños' }}</h3>
                <button wire:click="cancel" class="p-1 text-ink-400 hover:text-ink-700 rounded-md hover:bg-ink-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="save">
                <div class="modal-body">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Fecha *</label>
                            <input type="text" wire:model="fecha" class="input-field w-full" placeholder="DD/MM">
                            @error('fecha')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">DNI</label>
                            <div class="flex gap-2">
                                <input type="text" wire:model.live="dni" class="input-field flex-1" placeholder="8 dígitos" maxlength="8">
                                <span class="text-[11px] text-ink-400 font-medium whitespace-nowrap">Búsqueda:</span>
                                <select wire:model="proveedor" class="input-field w-24 text-xs px-1 py-1">
                                    @foreach($proveedores as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="consultarDni" class="btn btn-info text-sm" {{ strlen($dni) !== 8 ? 'disabled' : '' }}>Buscar</button>
                            </div>
                            @error('dni')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Nombre *</label>
                            <input type="text" wire:model="nombre" class="input-field w-full" placeholder="Nombre completo">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Parentesco</label>
                            <input type="text" wire:model="parentesco" class="input-field w-full" placeholder="Ej: HIJO, HIJA, ESPOSO, ESPOSA, NIETO, etc.">
                        </div>
                        <div>
                            <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Recordatorio</label>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="recordatorio_activo" class="w-4 h-4 text-ink-800 border-ink-300 rounded focus:ring-ink-400/30">
                                    <span class="text-sm text-ink-700">Activar recordatorio</span>
                                </label>
                            </div>
                        </div>
                        <div x-show="$wire.recordatorio_activo" x-transition>
                            <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Hora del recordatorio</label>
                            <input type="time" wire:model="recordatorio_hora" class="input-field w-32">
                            @error('recordatorio_hora')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Detalles</label>
                            <textarea wire:model="detalles" rows="3" class="input-field w-full" placeholder="Detalles del cumpleaños..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="cancel" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <svg wire:loading wire:target="save" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showProgramar)
    <div class="modal-overlay"
        x-data x-show="$wire.showProgramar"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="modal-card max-w-md"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="modal-header">
                <h3 class="font-semibold text-ink-900 dark:text-ink-100 text-sm font-display flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-4 h-4 text-[#5D87FF]"></i>
                    Programar recordatorio
                </h3>
                <button type="button" wire:click="cancelarProgramado" class="btn btn-ghost btn-xs p-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4 p-3 rounded-lg bg-ink-50 dark:bg-white/[0.06] border border-ink-100 dark:border-white/[0.06]">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-100 capitalize">{{ $programarNombre }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400 mt-0.5">Cumpleaños: <span class="font-medium">{{ $programarLimite }}</span> — el recordatorio debe programarse antes de esa fecha.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Fecha del recordatorio *</label>
                        <input type="date" wire:model="programarFecha" class="input-field w-full">
                        @error('programarFecha')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Hora del recordatorio *</label>
                        <input type="time" wire:model="programarHora" class="input-field w-32">
                        @error('programarHora')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="cancelarProgramado" class="btn btn-secondary">Cancelar</button>
                @if($programarId)
                    <button type="button" wire:click="eliminarProgramado" wire:confirm="¿Eliminar este recordatorio programado?" class="btn btn-danger">
                        Eliminar
                    </button>
                @endif
                <button type="button" wire:click="guardarProgramado" class="btn btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="guardarProgramado" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span wire:loading.remove wire:target="guardarProgramado">{{ $programarId ? 'Actualizar' : 'Programar' }}</span>
                    <span wire:loading wire:target="guardarProgramado">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($cumpleanosHoy))
    <div class="mb-6 rounded-xl border border-[#FFAE1F]/30 bg-amber-50 dark:bg-amber-500/5 p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0"><i data-lucide="cake" class="w-8 h-8 text-ember-500"></i></div>
            <div class="flex-1">
                    <h3 class="text-sm font-bold text-ember-800 uppercase tracking-wider mb-2 font-display">
                    ¡Cumpleaños de hoy!
                </h3>
                <div class="space-y-2">
                    @foreach($cumpleanosHoy as $c)
                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-[#1C1F2E] rounded-lg border border-amber-200/40 dark:border-amber-500/10">
                        <i data-lucide="party-popper" class="w-6 h-6 text-ember-500 shrink-0"></i>
                        <div class="flex-1">
                            <p class="font-bold text-ink-900 dark:text-ink-100 capitalize">{{ $c['nombre'] }}</p>
                            <p class="text-sm text-ink-600 dark:text-ink-400">
                                @if($c['parentesco'])
                                    <span class="capitalize">{{ $c['parentesco'] }}</span> —
                                @endif
                                @if(!empty($c['recordatorio_hora']))
                                    Recordatorio a las {{ substr($c['recordatorio_hora'], 0, 5) }}
                                @endif
                                @if($c['detalles'])
                                    — {{ $c['detalles'] }}
                                @endif
                            </p>
                        </div>
                        <span class="px-2 py-1 bg-amber-200/80 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 text-xs font-semibold rounded-full">HOY</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs text-ink-500 dark:text-ink-400 font-medium flex items-center gap-1">
                    @if($search)
                        Resultados para '{{ $search }}'
                        <span class="text-ink-300">({{ $total }})</span>
                    @else
                        Cumpleaños
                        <span class="text-ink-300">({{ $total }})</span>
                    @endif
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-400">Hoy {{ $countHoy }}</span>
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-400">≤7 días {{ $count7 }}</span>
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-400">≤30 días {{ $count30 }}</span>
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-600 dark:text-ink-300 font-semibold">Total {{ $total }}</span>
                </span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($selectMode)
                    <span class="text-xs text-ink-400 font-medium whitespace-nowrap">{{ count($selectedIds) }} seleccionados</span>
                @endif

                @if($selectMode && count($selectedIds) > 0)
                    <button wire:click="eliminarSeleccionados" class="btn btn-danger btn-sm">
                        Eliminar selección ({{ count($selectedIds) }})
                    </button>
                @endif

                <button wire:click="toggleSelectMode" class="btn btn-sm {{ $selectMode ? 'btn-warning' : 'btn-outline border-[#e5eaef]' }}">
                    @if($selectMode)
                        <i data-lucide="circle-x" class="w-4 h-4 mr-1.5 shrink-0"></i>
                        Cancelar
                    @else
                        <i data-lucide="check-square" class="w-4 h-4 mr-1.5 shrink-0"></i>
                        Seleccionar
                    @endif
                </button>
                <button wire:click="$dispatch('openImportModal')" class="btn btn-outline btn-sm border-[#e5eaef] text-ink-600 hover:bg-ink-50">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Importar
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte table-fixed w-full">
                    <thead>
                        <tr>
                            @if($selectMode)
                            <th class="w-10">
                                <input type="checkbox" wire:click="toggleSelectAll"
                                    wire:key="select-all-{{ count($selectedIds) === count($this->cumpleanos) && count($this->cumpleanos) > 0 ? 'on' : 'off' }}"
                                    {{ count($selectedIds) === count($this->cumpleanos) && count($this->cumpleanos) > 0 ? 'checked' : '' }}>
                            </th>
                            @endif
                            <th class="w-10">#</th>
                            <th class="w-36">Fecha</th>
                            <th class="w-20">Día</th>
                            <th>Nombre</th>
                            <th>Parentesco</th>
                            <th class="text-center w-28">Recordatorio</th>
                            <th>Detalles</th>
                            <th class="text-center w-24">Días</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $proxRank = 0; @endphp
                        @forelse($this->cumpleanos as $i => $c)
                            @php
                                $proximo = !$c['es_hoy'] && ($c['proximidad'] ?? 99) <= 7;
                                if ($proximo) $proxRank++;
                                $proxBg = $proximo ? ($proxRank === 1 ? 'bg-amber-400/75 dark:bg-amber-500/40' : ($proxRank === 2 ? 'bg-amber-400/50 dark:bg-amber-500/30' : 'bg-amber-400/30 dark:bg-amber-500/20')) : '';
                            @endphp
                            <tr class="transition-colors {{ $selectMode && in_array($c['id'], $selectedIds) ? 'bg-blue-100/60 dark:bg-blue-900/30' : ($c['es_hoy'] ? 'bg-amber-50 shadow-[inset_3px_0_0_0_#f59e0b]' : ($proximo ? $proxBg : ($i % 2 === 1 ? 'table-row-zebra' : ''))) }}">
                                @if($selectMode)
                                <td class="w-10" wire:click.stop="toggleSelect({{ $c['id'] }})">
                                    <input type="checkbox" wire:key="row-{{ $c['id'] }}-{{ in_array($c['id'], $selectedIds) ? 'on' : 'off' }}"
                                        {{ in_array($c['id'], $selectedIds) ? 'checked' : '' }}>
                                </td>
                                @endif
                                <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                                <td class="font-medium">
                                    @if($c['es_hoy'])
                                        <span class="text-ember-600 font-bold">🎂 {{ $c['fecha_larga'] ?? '' }}</span>
                                    @elseif($proximo)
                                        <span class="text-ember-700">📅 {{ $c['fecha_larga'] ?? '' }}</span>
                                    @else
                                        {{ $c['fecha_larga'] ?? '' }}
                                    @endif
                                </td>
                                <td class="text-ink-600 dark:text-ink-400 font-medium capitalize">@if(!empty($c['dia'])){{ $c['dia'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="font-medium text-ink-900 dark:text-ink-100 capitalize truncate min-w-0">
                                    @if(!empty($c['nombre'])){{ $c['nombre'] }}@else<span class="text-ink-300">—</span>@endif
                                    @if(!empty($c['alias'])) <span class="text-ink-400 text-xs font-normal">({{ $c['alias'] }})</span> @endif
                                    @if(!empty($c['es_personal']))
                                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-ember-100 text-ember-700">Personal</span>
                                    @endif
                                </td>
                                <td class="text-ink-500 dark:text-ink-400 capitalize truncate min-w-0">@if(!empty($c['parentesco'])){{ $c['parentesco'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="text-center">
                                    @if($c['recordatorio_activo'])
                                        <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                                            <i data-lucide="bell" class="w-4 h-4"></i>
                                            {{ $c['recordatorio_hora'] ?? '07:30' }}
                                        </span>
                                    @else
                                        <span class="text-ink-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="text-ink-500 dark:text-ink-400 max-w-[250px] truncate min-w-0 capitalize">@if(!empty($c['detalles'])){{ $c['detalles'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $c['proximidad'] <= 7 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-ink-100 text-ink-700 dark:text-ink-300' }}">
                                        {{ $c['proximidad'] }} día{{ $c['proximidad'] !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                @if(empty($c['es_personal']))
                                <td class="w-10" x-data>
                                    <button @click="$store.rowMenu.toggle('cum-{{ $c['id'] }}', $el); $event.stopImmediatePropagation()"
                                        :class="$store.rowMenu.opened('cum-{{ $c['id'] }}') ? 'bg-ink-100 dark:bg-white/[0.06] text-ink-700 dark:text-ink-200' : 'text-ink-400 hover:text-ink-700 dark:hover:text-ink-200'"
                                        class="p-1.5 rounded-md hover:bg-ink-100 dark:hover:bg-white/[0.06]">
                                        <i data-lucide="ellipsis-vertical" class="w-4 h-4"></i>
                                    </button>
                                    <div x-show="$store.rowMenu.opened('cum-{{ $c['id'] }}')" x-cloak
                                        x-effect="if ($store.rowMenu.opened('cum-{{ $c['id'] }}')) { $el.style.position = 'fixed'; $el.style.top = $store.rowMenu.top + 'px'; if ($store.rowMenu.right != null) { $el.style.right = $store.rowMenu.right + 'px'; $el.style.left = ''; } else { $el.style.left = $store.rowMenu.left + 'px'; $el.style.right = ''; } $el.style.zIndex = '9999'; }"
                                        class="w-44 bg-white dark:bg-[#1C1F2E] border border-[#e5eaef] dark:border-white/[0.06] rounded-lg shadow-lg py-1">
                                        <button wire:click="$dispatch('programar', { id: {{ $c['id'] }} })"
                                            class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                                            <i data-lucide="calendar-clock" class="w-4 h-4 shrink-0"></i>
                                            Recordatorio
                                        </button>
                                        <button wire:click="$dispatch('editar', { id: {{ $c['id'] }} })"
                                            class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                                            <i data-lucide="pencil" class="w-4 h-4 shrink-0"></i>
                                            Editar
                                        </button>
                                        <button wire:click="$dispatch('eliminar', { id: {{ $c['id'] }} })" wire:confirm="¿Eliminar este cumpleaños?"
                                            class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <i data-lucide="trash-2" class="w-4 h-4 shrink-0"></i>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                                @else
                                <td class="w-10"></td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectMode ? 10 : 9 }}" class="px-3 py-16 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i data-lucide="cake" class="w-8 h-8 text-ink-300 dark:text-white/20"></i>
                                        </div>
                                        <p class="empty-state-title">No hay cumpleaños registrados</p>
                                        <p class="empty-state-desc">Los cumpleaños aparecerán aquí una vez que se registren.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <livewire:olimpo.import-modal panel="cumpleanos" wire:key="import-cumpleanos" />
    </div>
    </div>
</div>

@push('scripts')
<script>
function cumpleanosComponent(birthdaysToday) {
    return {
        birthdaysToday: birthdaysToday || [],
        filtersOpen: false,
        checkReminder() {
            let now = new Date();
            let hhmm = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

            let today = this.birthdaysToday.filter(b => b.recordatorio_activo);
            today.forEach(b => {
                let hora = (b.recordatorio_hora || '07:30').substring(0, 5);
                let key = 'cumple_notified_' + b.id + '_' + now.toDateString();
                if (hhmm >= hora && !sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    this.fireNotification(b);
                }
            });
        },
        fireNotification(b) {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: {
                    message: '🎂 ¡Recordatorio de Cumpleaños! Hoy cumple: ' + b.nombre + (b.parentesco ? ' (' + b.parentesco + ')' : ''),
                    type: 'success'
                }
            }));

            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🎂 ¡Recordatorio de Cumpleaños!', {
                    body: 'Hoy cumple: ' + b.nombre + (b.parentesco ? ' (' + b.parentesco + ')' : ''),
                    icon: '/favicon.ico',
                });
            }

            this.playSound();
        },
        playSound() {
            try {
                let ctx = new (window.AudioContext || window.webkitAudioContext)();
                if (ctx.state === 'suspended') ctx.resume();
                let playTone = (freq, delay) => {
                    setTimeout(() => {
                        let osc = ctx.createOscillator();
                        let gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = freq;
                        osc.type = 'sine';
                        gain.gain.setValueAtTime(0.3, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.5);
                    }, delay);
                };
                playTone(800, 0);
                playTone(1000, 300);
            } catch (e) {
                console.warn('No se pudo reproducir el sonido:', e);
            }
        },
        requestPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },
        testNotification() {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: '🎂 ¡Recordatorio de prueba! Hoy cumple: Juan Pérez (HIJO)', type: 'success' }
            }));
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🎂 ¡Recordatorio de Cumpleaños!', {
                    body: 'Hoy cumple: Juan Pérez (HIJO)',
                    icon: '/favicon.ico',
                });
            }
            this.requestPermission();
            this.playSound();
        },
        init() {
            this.requestPermission();
            this.checkReminder();
            setInterval(() => this.checkReminder(), 30000);
        }
    };
}
</script>
@endpush
