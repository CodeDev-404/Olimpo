<div x-data="cumpleanosComponent(@entangle('cumpleanosHoy'))" x-init="init()">
    @if($showForm)
    <div class="modal-overlay"
        wire:click.self="cancel"
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
                                <input type="text" wire:model="dni" class="input-field flex-1" placeholder="8 dígitos" maxlength="8">
                                <span class="text-[11px] text-ink-400 font-medium whitespace-nowrap">Búsqueda:</span>
                                <select wire:model="proveedor" class="input-field w-24 text-xs px-1 py-1">
                                    @foreach($proveedores as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="consultarDni" class="btn btn-info text-sm" :disabled="dni.length !== 8">Buscar</button>
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
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(!empty($cumpleanosHoy))
    <div class="mb-5 rounded-xl border border-ember-300 bg-gradient-to-br from-ember-50 to-amber-50 px-5 py-4 shadow-[0_1px_3px_0_rgb(0_0_0_/_0.04)]">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-ember-200 text-xl">🎂</div>
            <div class="flex-1 min-w-0">
                <h3 class="text-[11px] font-bold text-ember-700 uppercase tracking-widest mb-2">Cumpleaños de hoy</h3>
                <div class="space-y-1.5">
                    @foreach($cumpleanosHoy as $c)
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🎉</span>
                        <span class="text-sm font-semibold text-ink-900 capitalize">{{ $c['nombre'] }}</span>
                        @if($c['parentesco'])
                        <span class="text-xs text-ink-500 capitalize">— {{ $c['parentesco'] }}</span>
                        @endif
                        @if($c['detalles'])
                        <span class="text-xs text-ink-400 capitalize">({{ $c['detalles'] }})</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="flex flex-wrap gap-2 mb-4">
                <button wire:click="nuevo" class="btn btn-primary btn-sm">+ Agregar</button>
                <button wire:click="editar" class="btn btn-secondary btn-sm">Editar</button>
                <button wire:click="eliminar" class="btn btn-danger btn-sm">Eliminar</button>
                <button wire:click="toggleSelectMode" class="btn btn-sm {{ $selectMode ? 'btn-warning' : 'btn-outline border-ink-200' }}">
                    {{ $selectMode ? 'Cancelar selección' : 'Seleccionar' }}
                </button>
                @if($selectMode)
                    <button wire:click="eliminarSeleccionados" class="btn btn-danger btn-sm">
                        Eliminar seleccionados ({{ count($selectedIds) }})
                    </button>
                @endif
                <button wire:click="$dispatch('openImportModal')" class="btn btn-outline btn-sm border-ink-200 text-ink-600 hover:bg-ink-50">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Importar
                </button>
                <button @click="testNotification()" class="btn btn-outline btn-sm border-ink-200 text-ink-600 hover:bg-ink-50" title="Probar notificación y sonido">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.035-.586 1.414L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Probar
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            @if($selectMode)
                            <th class="w-10">
                                <input type="checkbox" wire:click="toggleSelectAll"
                                    {{ count($selectedIds) === count($cumpleanos) && count($cumpleanos) > 0 ? 'checked' : '' }}>
                            </th>
                            @endif
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Día</th>
                            <th>Nombre</th>
                            <th>Parentesco</th>
                            <th>Recordatorio</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cumpleanos as $i => $c)
                            @php $proximo = !$c['es_hoy'] && ($c['proximidad'] ?? 99) <= 7; @endphp
                            <tr wire:click="selectCumpleano({{ $c['id'] }})" @if($selectMode) @click.stop @endif
                                class="cursor-pointer transition-colors {{ $c['es_hoy'] ? 'bg-amber-50 shadow-[inset_3px_0_0_0_#f59e0b]' : ($proximo ? 'bg-amber-50/40' : '') }} {{ $selectMode && in_array($c['id'], $selectedIds) ? 'bg-ink-50' : ($selectedId === $c['id'] ? 'bg-ink-50' : '') }}">
                            @if($selectMode)
                            <td class="w-10" wire:click.stop="toggleSelect({{ $c['id'] }})">
                                <input type="checkbox" {{ in_array($c['id'], $selectedIds) ? 'checked' : '' }}>
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
                            <td class="text-ink-600 font-medium capitalize">@if(!empty($c['dia'])){{ $c['dia'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="font-medium capitalize">
                                @if(!empty($c['nombre'])){{ $c['nombre'] }}@else<span class="text-ink-300">—</span>@endif
                                @if(!empty($c['es_personal']))
                                    <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-ember-100 text-ember-700">Personal</span>
                                @endif
                            </td>
                            <td class="text-ink-500 font-medium capitalize">@if(!empty($c['parentesco'])){{ $c['parentesco'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-center">
                                @if($c['recordatorio_activo'])
                                    <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.035-.586 1.414L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                        {{ $c['recordatorio_hora'] ?? '07:30' }}
                                    </span>
                                @else
                                    <span class="text-ink-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="text-ink-500 max-w-[300px] truncate capitalize">@if(!empty($c['detalles'])){{ $c['detalles'] }}@else<span class="text-ink-300">—</span>@endif</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectMode ? 8 : 7 }}" class="px-3 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2 text-ink-400">
                                        <span class="text-2xl">📋</span>
                                        <span class="text-sm">No hay cumpleaños registrados</span>
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

@push('scripts')
<script>
function cumpleanosComponent(birthdaysToday) {
    return {
        birthdaysToday: birthdaysToday,
        checkReminder() {
            let today = this.birthdaysToday.filter(b => b.recordatorio_activo);
            if (today.length === 0) return;

            let now = new Date();
            let hhmm = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

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
            this.fireNotification({ id: 'test', nombre: 'PRUEBA', parentesco: 'TEST', recordatorio_hora: '07:30' });
            this.requestPermission();
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
