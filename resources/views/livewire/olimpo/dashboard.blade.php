<div>
    {{-- Enlaces rápidos --}}
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <a href="{{ route('olimpo.asistencia') }}" class="btn btn-sm btn-outline">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Asistencia
        </a>
        <a href="{{ route('olimpo.ocurrencias') }}" class="btn btn-sm btn-outline">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Ocurrencias
        </a>
        <a href="{{ route('olimpo.personal') }}" class="btn btn-sm btn-outline">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Personal
        </a>
        <a href="{{ route('olimpo.config') }}" class="btn btn-sm btn-outline">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Ajustes
        </a>
        <span class="ml-auto text-xs text-ink-400">Dashboard</span>
    </div>

    {{-- Cumpleaños del día --}}
    @if(count($cumpleanos) > 0)
    <div class="mb-6 rounded-lg border-2 border-ember-400 bg-gradient-to-r from-amber-50 to-yellow-50 p-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 text-3xl">🎂</div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider mb-2">¡Cumpleaños de hoy!</h3>
                <div class="space-y-2">
                    @foreach($cumpleanos as $c)
                    <div class="flex items-center gap-3 p-3 bg-white rounded-lg shadow-sm border border-amber-200">
                        <span class="text-2xl">🎉</span>
                        <div class="flex-1">
                            <p class="font-bold text-ink-900 capitalize">{{ $c['nombre'] }}</p>
                            @if($c['parentesco'])
                                <p class="text-sm text-ink-600 capitalize">{{ $c['parentesco'] }}</p>
                            @endif
                        </div>
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">HOY</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Estadísticas --}}
    <div class="flex items-center justify-between mb-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
            <div class="stat-card border-l-[3px] border-l-red-500">
                <p class="stat-label">Hoy</p>
                <div class="flex items-end justify-between mt-1">
                    <p class="stat-value">{{ $stats['hoy'] ?? 0 }}</p>
                    <svg class="w-8 h-8 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[11px] text-ink-400 mt-1">Ocurrencias hoy</p>
            </div>
            <div class="stat-card border-l-[3px] border-l-ember-500">
                <p class="stat-label">Semana</p>
                <div class="flex items-end justify-between mt-1">
                    <p class="stat-value">{{ $stats['semana'] ?? 0 }}</p>
                    <svg class="w-8 h-8 text-ember-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <p class="text-[11px] text-ink-400 mt-1">Ocurrencias en 7 días</p>
            </div>
            <div class="stat-card border-l-[3px] border-l-green-500">
                <p class="stat-label">Mes</p>
                <div class="flex items-end justify-between mt-1">
                    <p class="stat-value">{{ $stats['mes'] ?? 0 }}</p>
                    <svg class="w-8 h-8 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-[11px] text-ink-400 mt-1">Ocurrencias del mes</p>
            </div>
            <div class="stat-card border-l-[3px] border-l-blue-500">
                <p class="stat-label">Total</p>
                <div class="flex items-end justify-between mt-1">
                    <p class="stat-value">{{ $stats['total'] ?? 0 }}</p>
                    <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <p class="text-[11px] text-ink-400 mt-1">Registros históricos</p>
            </div>
        </div>
    </div>

    {{-- Búsqueda y acciones --}}
    <div class="card mb-5">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" wire:model.live="search" placeholder="Buscar ocurrencia..."
                        class="input-field pl-9 w-full" />
                </div>
                @if($search)
                    <button wire:click="$set('search', '')" class="btn btn-ghost btn-sm">Limpiar</button>
                @endif
                <button wire:click="newQuickOcurrencia" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Nueva Ocurrencia
                </button>
            </div>
        </div>
    </div>

    {{-- Timeline / Listado --}}
    <div class="card" x-data="{ popTitle: '', popContent: '', showPop: false }">
        <div class="card-header">
            <span class="text-xs text-ink-500 font-medium">
                {{ $search ? "Resultados para '{$search}' (" . count($ocurrencias) . ')' : 'Últimas 24 horas — ' . count($ocurrencias) . ' registro(s)' }}
            </span>
            <div class="flex items-center gap-1.5 text-[11px] text-ink-400">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-500"></span> En vivo
            </div>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Nombre</th>
                            <th>Detalles</th>
                            <th>Obs.</th>
                            <th>Tipo</th>
                            <th>Turno</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ocurrencias as $i => $oc)
                            <tr wire:click="selectOcurrencia({{ $oc['id'] }})"
                                class="cursor-pointer {{ $selectedId === $oc['id'] ? 'bg-ink-50' : '' }}">
                                <td>
                                    <span class="w-2 h-2 rounded-full inline-block {{ $oc['fecha'] === now()->format('d/m/Y') ? 'bg-green-400' : 'bg-ink-300' }}"></span>
                                </td>
                                <td class="font-medium text-xs">@if(!empty($oc['fecha'])){{ $oc['fecha'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="tabular-nums text-xs">
                                    @if(!empty($oc['hora_ingreso'])){{ $oc['hora_ingreso'] }}@else<span class="text-ink-300">—</span>@endif@if(!empty($oc['hora_salida']))-{{ $oc['hora_salida'] }}@endif
                                </td>
                                <td class="font-medium">@if(!empty($oc['persona_nombre'])){{ $oc['persona_nombre'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="max-w-[160px] truncate text-ink-500 text-xs cursor-pointer"
                                    @dblclick="popContent = $el.dataset.content; popTitle = 'Detalles'; showPop = true"
                                    data-content="{{ $oc['detalles'] ?? '' }}">
                                    @if(!empty($oc['detalles'])){{ $oc['detalles'] }}@else<span class="text-ink-300">—</span>@endif
                                </td>
                                <td class="max-w-[100px] truncate text-ink-400 italic text-xs cursor-pointer"
                                    @dblclick="popContent = $el.dataset.content; popTitle = 'Observación'; showPop = true"
                                    data-content="{{ $oc['observacion'] ?? '' }}">
                                    @if(!empty($oc['observacion'])){{ $oc['observacion'] }}@else<span class="text-ink-300">—</span>@endif
                                </td>
                                <td>
                                    @if($oc['tipo'])
                                        <span class="badge text-[10px]" style="background: {{ $this->tipoColor($oc['tipo']) }}18; color: {{ $this->tipoColor($oc['tipo']) }}">
                                            {{ $oc['tipo'] }}
                                        </span>
                                    @else
                                        <span class="text-ink-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1 text-[10px] text-ink-500">
                                        @php
                                            $_turno = $oc['turno'] ?? null;
                                            $_tc = match($_turno) { 'NOCHE' => 'bg-blue-900', 'DÍA' => 'bg-sky-400', default => 'bg-ink-300' };
                                        @endphp
                                        <span class="w-2 h-2 rounded-full inline-block {{ $_tc }}"></span>
                                        @if(!empty($_turno)){{ $_turno }}@else<span class="text-ink-300">—</span>@endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-12 text-center text-ink-400">
                                    No hay ocurrencias registradas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div x-show="showPop" x-cloak
            class="modal-overlay"
            @click.self="showPop = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="modal-card max-w-lg"
                @click.stop
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="modal-header">
                    <h4 class="font-semibold text-ink-900 text-sm" x-text="popTitle"></h4>
                    <button @click="showPop = false" class="p-1 text-ink-400 hover:text-ink-700 rounded-md hover:bg-ink-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-4 overflow-y-auto max-h-[60vh] text-sm text-ink-700 leading-relaxed whitespace-pre-wrap" x-text="popContent"></div>
            </div>
        </div>
    </div>

    {{-- Quick Form Modal --}}
    @if($showQuickForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape.window="$wire.cancelQuickForm">
        <div class="bg-white rounded-xl shadow-[0_8px_32px_rgb(0_0_0_/_0.12)] w-full max-w-lg max-h-[90vh] overflow-y-auto mx-4">
            <div class="flex items-center justify-between px-6 py-4">
                <h4 class="font-semibold text-ink-900 text-sm">Nueva Ocurrencia</h4>
                <button wire:click="cancelQuickForm" class="p-1 text-ink-400 hover:text-ink-700 rounded-md hover:bg-ink-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Persona</label>
                    <input type="text" wire:model="qf_persona_nombre" list="qf-nombres-list" class="input-field w-full" placeholder="Escribe el nombre..." autocomplete="off" />
                    <datalist id="qf-nombres-list">
                        @foreach($nombres as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </datalist>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Turno</label>
                        <select wire:model.live="qf_turno" class="input-field w-full">
                            <option value="DÍA">Día</option>
                            <option value="NOCHE">Noche</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Tipo</label>
                        <select wire:model="qf_tipo" class="input-field w-full">
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
                        <input type="time" wire:model="qf_hora_ingreso" class="input-field w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Hora Salida</label>
                        <input type="time" wire:model="qf_hora_salida" class="input-field w-full" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Otro</label>
                    <input type="text" wire:model="qf_otro" class="input-field w-full" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Detalles</label>
                    <textarea wire:model="qf_detalles" class="input-field w-full" rows="2"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-500 uppercase tracking-wider mb-1">Observación</label>
                    <textarea wire:model="qf_observacion" class="input-field w-full" rows="2"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-ink-100">
                <button wire:click="cancelQuickForm" class="btn btn-secondary btn-sm">Cancelar</button>
                <button wire:click="saveQuickOcurrencia" class="btn btn-primary btn-sm">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>
