<div>
    @if(!$isAuth)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-5 text-sm">
            <span class="font-semibold">Bloqueado</span> — Solo administradores pueden modificar la configuración.
        </div>
    @endif

    <div class="flex flex-wrap gap-1 mb-5 bg-ink-100 p-1 rounded-lg">
        @foreach([
            ['key' => 'tipos', 'label' => 'Tipos'],
            ['key' => 'cargos', 'label' => 'Cargos'],
            ['key' => 'camionetas', 'label' => 'Camionetas'],
            ['key' => 'usuarios', 'label' => 'Usuarios'],
            ['key' => 'settings', 'label' => 'Ajustes'],
        ] as $tab)
            <button wire:click="setTab('{{ $tab['key'] }}')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                {{ $activeTab === $tab['key'] ? 'bg-white text-ink-800 shadow-sm' : 'text-ink-500 hover:text-ink-700 hover:bg-white/50' }}">
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    @if($activeTab === 'tipos')
        <div class="card">
            <div class="card-body">
                @if($isAuth)
                    <button wire:click="newTipo" class="btn btn-primary mb-4">+ Nuevo Tipo</button>
                @endif
                @if($showTipoForm)
                    <div class="bg-ink-50 border border-ink-200 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Nombre</label>
                                <input type="text" wire:model="tipoNombre" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Nivel</label>
                                <select wire:model="tipoNivel" class="input-field w-full">
                                    <option value="">Sin especificar</option>
                                    @foreach($niveles as $n)
                                        <option value="{{ $n }}">{{ $n }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Color</label>
                                <input type="color" wire:model="tipoColor" class="h-10 w-full rounded-lg cursor-pointer border border-ink-200">
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="tipoActivo" class="rounded border-ink-300 text-ink-800">
                                    <span class="text-sm text-ink-700">Activo</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button wire:click="$set('showTipoForm', false)" class="btn btn-secondary">Cancelar</button>
                            <button wire:click="saveTipo" class="btn btn-primary" wire:loading.attr="disabled">Guardar</button>
                        </div>
                    </div>
                @endif
                <div class="space-y-2">
                    @foreach($tipos as $t)
                        <div class="flex items-center justify-between bg-ink-50 border border-ink-100 p-3 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="w-4 h-4 rounded" style="background: {{ $t['color'] }}"></span>
                                <span class="font-medium text-ink-900">{{ $t['nombre'] }}</span>
                                <span class="text-xs text-ink-400">{{ $t['nivel'] }}</span>
                                <span class="badge {{ $t['activo'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $t['activo'] ? 'ACTIVO' : 'INACTIVO' }}
                                </span>
                            </div>
                            @if($isAuth)
                                <div class="flex gap-2">
                                    <button wire:click="editTipo({{ $t['id'] }})" class="text-xs text-ink-600 hover:underline">Editar</button>
                                    <button wire:click="deleteTipo({{ $t['id'] }})" class="text-xs text-red-600 hover:underline">Eliminar</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'cargos')
        <div class="card">
            <div class="card-body">
                @if($isAuth)
                    <button wire:click="newCargo" class="btn btn-primary mb-4">+ Nuevo Cargo</button>
                @endif
                @if($showCargoForm)
                    <div class="bg-ink-50 border border-ink-200 p-4 rounded-lg mb-4 flex gap-2 items-start">
                        <div class="flex-1 space-y-2">
                            <input type="text" wire:model="cargoNombre" class="input-field w-full" placeholder="Nombre del cargo">
                            <div class="flex gap-2">
                                <select wire:model="cargoGrupo" class="input-field flex-1">
                                    <option value="OLIMPO">OLIMPO</option>
                                    <option value="CHOFERES">CHOFERES</option>
                                    <option value="COCINA">COCINA</option>
                                    <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                                </select>
                                <input type="number" wire:model="cargoOrden" class="input-field" style="width:70px" placeholder="Orden" min="0">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button wire:click="$set('showCargoForm', false)" class="btn btn-secondary">Cancelar</button>
                            <button wire:click="saveCargo" class="btn btn-primary" wire:loading.attr="disabled">Guardar</button>
                        </div>
                    </div>
                @endif
                <div class="space-y-2">
                    @foreach($cargos as $c)
                        <div class="flex items-center justify-between bg-ink-50 border border-ink-100 p-3 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-ink-900">{{ $c['nombre'] }}</span>
                                <span class="badge {{ $c['grupo'] === 'CHOFERES' ? 'bg-blue-100 text-blue-700' : ($c['grupo'] === 'COCINA' ? 'bg-amber-100 text-amber-700' : ($c['grupo'] === 'MANTENIMIENTO' ? 'bg-green-100 text-green-700' : 'bg-ink-100 text-ink-600')) }}">
                                    {{ $c['grupo'] ?? 'OLIMPO' }}
                                </span>
                                @if($c['orden'] > 0)
                                    <span class="text-xs text-ink-400">#{{ $c['orden'] }}</span>
                                @endif
                            </div>
                            @if($isAuth)
                                <div class="flex gap-2">
                                    <button wire:click="editCargo({{ $c['id'] }})" class="text-xs text-ink-600 hover:underline">Editar</button>
                                    <button wire:click="deleteCargo({{ $c['id'] }})" class="text-xs text-red-600 hover:underline">Eliminar</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'camionetas')
        <div class="card">
            <div class="card-body">
                @if($isAuth)
                    <button wire:click="newCamioneta" class="btn btn-primary mb-4">+ Nueva Camioneta</button>
                @endif
                @if($showCamForm)
                    <div class="bg-ink-50 border border-ink-200 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Placa *</label>
                                <input type="text" wire:model="camPlaca" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Marca</label>
                                <input type="text" wire:model="camMarca" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Modelo</label>
                                <input type="text" wire:model="camModelo" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Año</label>
                                <input type="text" wire:model="camAnio" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Color</label>
                                <input type="text" wire:model="camColor" class="input-field w-full">
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button wire:click="$set('showCamForm', false)" class="btn btn-secondary">Cancelar</button>
                            <button wire:click="saveCamioneta" class="btn btn-primary" wire:loading.attr="disabled">Guardar</button>
                        </div>
                    </div>
                @endif
                <div class="space-y-2">
                    @foreach($camionetas as $c)
                        <div class="flex items-center justify-between bg-ink-50 border border-ink-100 p-3 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-medium text-ink-900">{{ $c['placa'] }}</span>
                                <span class="text-sm text-ink-500">{{ $c['marca'] }} {{ $c['modelo'] }} ({{ $c['anio'] }})</span>
                                <span class="badge {{ $c['estado'] === 'ACTIVO' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $c['estado'] }}
                                </span>
                            </div>
                            @if($isAuth)
                                <div class="flex items-center gap-2">
                                    <button wire:click="editCamioneta({{ $c['id'] }})" class="text-xs text-ink-600 hover:underline">Editar</button>
                                    <button wire:click="deleteCamioneta({{ $c['id'] }})" class="text-xs text-red-600 hover:underline">Eliminar</button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'usuarios')
        <div class="card">
            <div class="card-body">
                @if($isAuth)
                    <button wire:click="newUser" class="btn btn-primary mb-4">+ Nuevo Usuario</button>
                @endif
                @if($showUserForm)
                    <div class="bg-ink-50 border border-ink-200 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Usuario *</label>
                                <input type="text" wire:model="userUsername" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Contraseña</label>
                                <input type="password" wire:model="userPassword" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Nombre completo</label>
                                <input type="text" wire:model="userName" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Rol</label>
                                <select wire:model="userRole" class="input-field w-full">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button wire:click="$set('showUserForm', false)" class="btn btn-secondary">Cancelar</button>
                            <button wire:click="saveUser" class="btn btn-primary" wire:loading.attr="disabled">Guardar</button>
                        </div>
                    </div>
                @endif
                <div class="space-y-2">
                    @foreach($usuarios as $u)
                        <div class="flex items-center justify-between bg-ink-50 border border-ink-100 p-3 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-ink-900">{{ $u['name'] }}</span>
                                <span class="text-sm text-ink-400">({{ $u['email'] }})</span>
                                <span class="badge {{ $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-ink-100 text-ink-600' }}">
                                    {{ $u['role'] === 'admin' ? 'Admin' : 'User' }}
                                </span>
                            </div>
                            @if($isAuth)
                                <button wire:click="deleteUser({{ $u['id'] }})" class="text-xs text-red-600 hover:underline">Eliminar</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'settings')
        <div class="card">
            <div class="card-body">
                <div class="space-y-6 max-w-lg">
                    <div>
                        <h4 class="text-xs font-semibold text-ink-500 uppercase tracking-wider mb-3">Turno Día</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Hora entrada</label>
                                <input type="time" wire:model="horaEntradaDia" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Hora salida</label>
                                <input type="time" wire:model="horaSalidaDia" class="input-field w-full">
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-ink-500 uppercase tracking-wider mb-3">Turno Noche</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Hora entrada</label>
                                <input type="time" wire:model="horaEntradaNoche" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Hora salida</label>
                                <input type="time" wire:model="horaSalidaNoche" class="input-field w-full">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Límite BUENO (min)</label>
                                <input type="number" wire:model="limiteBuenoMin" class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-ink-500 font-semibold uppercase tracking-wider mb-1">Límite REGULAR (min)</label>
                                <input type="number" wire:model="limiteRegularMin" class="input-field w-full">
                            </div>
                        </div>
                    </div>
                </div>
                <button wire:click="saveSettings" class="btn btn-primary mt-6">Guardar Configuración</button>
            </div>
        </div>
    @endif
</div>
