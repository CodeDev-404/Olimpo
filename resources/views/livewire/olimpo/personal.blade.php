<div>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-initial">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live="search" placeholder="Buscar persona..."
                    class="input-field pl-9 w-full sm:w-56" />
            </div>
        </div>
        <button wire:click="nueva" class="btn btn-primary btn-sm w-full sm:w-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo Personal
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="text-xs text-ink-500 font-medium">
                Todo el personal <span class="text-ink-300">({{ count($personal) }})</span>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Área</th>
                            <th>Cumpleaños</th>
                            <th>Celular</th>
                            <th>DNI</th>
                            <th>Edad</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($personal as $i => $p)
                            <tr wire:click="selectPersona({{ $p['id'] }})"
                                class="cursor-pointer {{ $selectedId === $p['id'] ? 'bg-ink-50' : '' }}">
                                <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                                <td class="font-medium">
                                    @if(!empty($p['nombre'])){{ $p['nombre'] }}@else<span class="text-ink-300">—</span>@endif
                                    @if(!empty($p['alias']))
                                        <span class="text-ink-400 text-xs ml-1">({{ $p['alias'] }})</span>
                                    @endif
                                </td>
                                <td class="text-ink-600">@if(!empty($p['cargo'])){{ $p['cargo'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="text-ink-500">@if(!empty($p['departamento'])){{ $p['departamento'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="tabular-nums text-ink-500">@if(!empty($p['cumpleaños_format'])){{ $p['cumpleaños_format'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="tabular-nums">@if(!empty($p['telefono'])){{ $p['telefono'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="tabular-nums text-ink-500">@if(!empty($p['documento'])){{ $p['documento'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td class="text-ink-500">@if(!empty($p['edad'])){{ $p['edad'] }}@else<span class="text-ink-300">—</span>@endif</td>
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
                                    No hay personal registrado
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape.window="$wire.cancel"
        x-data
        wire:key="form-modal">
        <div class="bg-white rounded-xl shadow-[0_8px_32px_rgb(0_0_0_/_0.12)] w-full max-w-lg max-h-[90vh] overflow-y-auto mx-4">
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
