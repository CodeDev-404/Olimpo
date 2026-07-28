<div>
    <div class="card mb-5">
        <div class="card-body">
            <div class="mb-5">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-ink-600 dark:text-ink-400 shrink-0">Búsqueda por:</span>
                    <div class="flex rounded-lg border border-[#e5eaef] dark:border-white/[0.06] overflow-hidden shrink-0" role="tablist">
                        <button type="button" wire:click="cambiarModo('simple')"
                            class="px-4 py-2 text-sm font-medium transition-all {{ $modo === 'simple' ? 'bg-[#1C1F2E] text-white' : 'bg-white dark:bg-[#1C1F2E] text-ink-600 dark:text-white/60 hover:bg-ink-50 dark:hover:bg-white/[0.06]' }}">
                            Simple
                        </button>
                        <button type="button" wire:click="cambiarModo('premium')"
                            class="px-4 py-2 text-sm font-medium transition-all {{ $modo === 'premium' ? 'bg-[#1C1F2E] text-white' : 'bg-white dark:bg-[#1C1F2E] text-ink-600 dark:text-white/60 hover:bg-ink-50 dark:hover:bg-white/[0.06]' }}">
                            Premium
                        </button>
                    </div>

                    @if($modo === 'premium')
                    <select wire:change="seleccionarHerramienta($event.target.value)"
                        class="input-field max-w-[220px]">
                        @foreach($premiumHerramientas as $t)
                            <option value="{{ $t['id'] }}" {{ $herramienta === $t['id'] ? 'selected' : '' }}>{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
            </div>

            @php
                $isSimple = $modo === 'simple';
                $inputLabel = $isSimple
                    ? ($tipo === 'dni' ? 'Ingrese DNI (8 dígitos)' : 'Ingrese RUC (11 dígitos)')
                    : match(collect($herramientas)->firstWhere('id', $herramienta)['input'] ?? 'dni') {
                        'dni' => 'Ingrese DNI (8 dígitos)',
                        'ruc' => 'Ingrese RUC (11 dígitos)',
                        'plate' => 'Ingrese Placa (ej: ABC-123)',
                        'name' => 'Ingrese nombres a buscar',
                        default => 'Ingrese documento',
                    };
                $isNameSearch = !$isSimple && (collect($herramientas)->firstWhere('id', $herramienta)['input'] ?? '') === 'name';
                $maxlength = match(!$isSimple ? (collect($herramientas)->firstWhere('id', $herramienta)['input'] ?? 'dni') : ($tipo === 'ruc' ? 11 : 8)) { 'dni' => 8, 'ruc' => 11, default => 20 };
            @endphp

            <div class="flex items-center gap-3">
                @if($isSimple)
                <div class="flex rounded-lg border border-[#e5eaef] dark:border-white/[0.06] overflow-hidden shrink-0">
                    <button type="button" wire:click="$set('tipo', 'dni')"
                        class="px-4 py-2 text-sm font-medium {{ $tipo === 'dni' ? 'bg-[#1C1F2E] text-white' : 'bg-white dark:bg-[#1C1F2E] text-ink-600 dark:text-white/60 hover:bg-ink-50 dark:hover:bg-white/[0.06]' }}">
                        DNI
                    </button>
                    <button type="button" wire:click="$set('tipo', 'ruc')"
                        class="px-4 py-2 text-sm font-medium {{ $tipo === 'ruc' ? 'bg-[#1C1F2E] text-white' : 'bg-white dark:bg-[#1C1F2E] text-ink-600 dark:text-white/60 hover:bg-ink-50 dark:hover:bg-white/[0.06]' }}">
                        RUC
                    </button>
                </div>
                @endif
                <input type="text"
                    @if($isNameSearch) wire:model="searchTerm" @else wire:model="documento" @endif
                    maxlength="{{ $maxlength }}"
                    placeholder="{{ $inputLabel }}"
                    class="input-field flex-1" />
                <button type="button" wire:click="consultar" wire:loading.attr="disabled"
                    class="btn btn-primary">
                    <i data-lucide="search" class="w-4 h-4 shrink-0"></i>
                    Consultar
                </button>
            </div>
            @error('documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            @error('searchTerm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Result modal for all tools --}}
    <div x-data="{ open: $wire.entangle('showModal') }"
         x-show="open"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/60 p-4"
         @keydown.escape.window="open = false">
        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="open = false">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#e5eaef] dark:border-white/[0.06] shrink-0">
                <h3 class="text-base font-bold text-ink-900 dark:text-white tracking-wide font-display">{{ $modalTitle }}</h3>
                <button @click="open = false" class="text-ink-400 hover:text-ink-600 dark:text-white/60 text-2xl leading-none w-8 h-8 flex items-center justify-center rounded hover:bg-ink-100 dark:hover:bg-white/[0.06]">&times;</button>
            </div>
            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6">
                @if(isset($resultado['error']))
                    <p class="text-ink-500 text-center py-8">No se encontraron resultados para esta consulta.</p>
                @elseif($herramienta === 'kmente' && !empty($resultado['foto']))
                    {{-- Premium modal with photo/firma sidebar --}}
                    <div class="flex flex-col lg:flex-row gap-6">
                        <div class="flex-1 min-w-0">
                            <table class="table-adminlte w-full">
                                <thead>
                                    <tr>
                                        <th class="w-2/5">Campo</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">DNI</td><td>{{ $resultado['dni'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Nombres completos</td><td>{{ $resultado['nombre_completo'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Fecha de nacimiento</td><td>{{ $resultado['feNacimiento'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Edad</td><td>{{ $resultado['nuEdad'] ?? '—' }} @if(!empty($resultado['nuEdad'])) años @endif</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Sexo</td><td class="capitalize">{{ $resultado['sexo'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Estado civil</td><td class="capitalize">{{ $resultado['estadoCivil'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Lugar de nacimiento</td><td class="capitalize">{{ $resultado['lugar_nacimiento'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Dirección</td><td class="capitalize">{{ $resultado['Direccion'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Grado de instrucción</td><td class="capitalize">{{ $resultado['gradoInstruccion'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Estatura</td><td>{{ $resultado['estatura'] ?? '—' }} @if(!empty($resultado['estatura'])) cm @endif</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Donante de órganos</td><td class="capitalize">{{ $resultado['donaOrganos'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Restricción</td><td class="capitalize">{{ $resultado['deRestriccion'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Padre</td><td class="capitalize">{{ $resultado['nomPadre'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Madre</td><td class="capitalize">{{ $resultado['nomMadre'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Fecha de emisión</td><td>{{ $resultado['feEmision'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Fecha de caducidad</td><td>{{ $resultado['feCaducidad'] ?? '—' }}</td></tr>
                                    <tr><td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">Fecha de inscripción</td><td>{{ $resultado['feInscripcion'] ?? '—' }}</td></tr>
                                </tbody>
                            </table>
                            @if(!empty($resultado['foto']) || !empty($resultado['firma']))
                            <div class="flex gap-4 mt-4 lg:hidden">
                                @if(!empty($resultado['foto']))
                                <div class="flex-1 border border-[#e5eaef] dark:border-white/[0.06] rounded-lg overflow-hidden">
                                    <div class="bg-[#f4f6f9] dark:bg-white/[0.06] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Foto</div>
                                    <div class="p-2">
                                        <img src="data:image/jpeg;base64,{{ $resultado['foto'] }}" alt="Foto" class="w-full h-auto">
                                    </div>
                                </div>
                                @endif
                                @if(!empty($resultado['firma']))
                                <div class="flex-1 border border-[#e5eaef] dark:border-white/[0.06] rounded-lg overflow-hidden">
                                    <div class="bg-[#f4f6f9] dark:bg-white/[0.06] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Firma</div>
                                    <div class="p-2">
                                        <img src="data:image/jpeg;base64,{{ $resultado['firma'] }}" alt="Firma" class="w-full h-auto">
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @if(!empty($resultado['foto']) || !empty($resultado['firma']))
                        <aside class="hidden lg:flex lg:flex-col gap-4 w-48 shrink-0">
                            @if(!empty($resultado['foto']))
                            <div class="border border-[#e5eaef] dark:border-white/[0.06] rounded-lg overflow-hidden">
                                <div class="bg-[#f4f6f9] dark:bg-white/[0.06] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Foto</div>
                                <div class="p-2">
                                    <img src="data:image/jpeg;base64,{{ $resultado['foto'] }}" alt="Foto" class="w-full h-auto">
                                </div>
                            </div>
                            @endif
                            @if(!empty($resultado['firma']))
                            <div class="border border-[#e5eaef] dark:border-white/[0.06] rounded-lg overflow-hidden">
                                <div class="bg-[#f4f6f9] dark:bg-white/[0.06] px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-ink-400">Firma</div>
                                <div class="p-2">
                                    <img src="data:image/jpeg;base64,{{ $resultado['firma'] }}" alt="Firma" class="w-full h-auto">
                                </div>
                            </div>
                            @endif
                        </aside>
                        @endif
                    </div>
                @else
                    {{-- Generic key-value table for all other tools --}}
                    <table class="table-adminlte w-full">
                        <thead>
                            <tr>
                                <th class="w-2/5">Campo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(collect($resultado)->except(['_proveedor', 'foto', 'firma', '_token'])->filter(fn($v) => $v !== null && $v !== '') as $campo => $valor)
                                <tr>
                                    <td class="font-semibold text-ink-400 text-[11px] uppercase tracking-wider">{{ preg_replace('/([a-z])([A-Z])/', '$1 $2', $campo) }}</td>
                                    <td>
                                        @if(is_array($valor) || is_object($valor))
                                            {{ json_encode($valor) }}
                                        @elseif(str_starts_with((string)$valor, '/9j/') || str_starts_with((string)$valor, 'iVBOR'))
                                            <span class="text-ink-300 italic">(imagen base64)</span>
                                        @else
                                            {{ $valor }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-3 py-8 text-center text-ink-400">Sin datos para mostrar</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="font-semibold text-sm text-ink-900 dark:text-white font-display">Historial de Consultas</span>
            @if(count($historial) > 0)
                <button type="button" wire:click="limpiarHistorial" wire:confirm="¿Eliminar todo el historial?" class="btn btn-danger btn-xs">
                    Limpiar historial
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            <table class="table-adminlte">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Documento</th>
                        <th>Nombre / Razón Social</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historial as $i => $h)
                        <tr class="cursor-pointer" wire:click="verResultado({{ $i }})">
                            <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                            <td>
                                @if(!empty($h['tipo']))
                                <span class="badge text-xs {{ $h['tipo'] === 'DNI' || $h['tipo'] === 'SUNAT' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' }}">
                                    {{ $h['tipo'] }}
                                </span>
                                @else
                                <span class="text-ink-300">—</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs">@if(!empty($h['documento'])){{ $h['documento'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="font-medium">@if(!empty($h['nombre_mostrar'])){{ $h['nombre_mostrar'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-xs text-ink-400">@if(!empty($h['created_at'])){{ \Carbon\Carbon::parse($h['created_at'])->format('d/m/Y H:i') }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td>
                                <i data-lucide="circle" class="w-4 h-4 text-ink-400"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-16 text-center">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i data-lucide="search" class="w-8 h-8 text-ink-300 dark:text-white/20"></i>
                                    </div>
                                    <p class="empty-state-title">No hay consultas registradas</p>
                                    <p class="empty-state-desc">Las consultas aparecerán aquí una vez que se realicen.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
