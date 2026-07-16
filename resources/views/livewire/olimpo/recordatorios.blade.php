<div x-data="recordatoriosComponent()">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-base font-semibold text-ink-900">Recordatorios de Cumpleaños</h2>
        <button @click="testNotification()" class="btn btn-info btn-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.035-.586 1.414L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Probar Notificación
        </button>
    </div>

    @if(!empty($cumpleanosHoy))
    <div class="mb-6 rounded-lg border-2 border-ember-400 bg-gradient-to-r from-amber-50 to-yellow-50 p-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 text-3xl">🎂</div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider mb-2">
                    ¡Cumpleaños de hoy con recordatorio activo!
                </h3>
                <div class="space-y-2">
                    @foreach($cumpleanosHoy as $c)
                    <div class="flex items-center gap-3 p-3 bg-white rounded-lg shadow-sm border border-amber-200">
                        <span class="text-2xl">🎉</span>
                        <div class="flex-1">
                            <p class="font-bold text-ink-900 capitalize">{{ $c['nombre'] }}</p>
                            <p class="text-sm text-ink-600">
                                @if($c['parentesco'])
                                    <span class="capitalize">{{ $c['parentesco'] }}</span> —
                                @endif
                                Recordatorio a las {{ substr($c['recordatorio_hora'], 0, 5) }}
                                @if($c['detalles'])
                                    — {{ $c['detalles'] }}
                                @endif
                            </p>
                        </div>
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">HOY</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($proximosRecordatorios))
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-ink-900">Próximos cumpleaños con recordatorio</h3>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Día</th>
                            <th>Nombre</th>
                            <th>Parentesco</th>
                            <th class="text-center">Recordatorio</th>
                            <th>Detalles</th>
                            <th class="text-center">Días</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proximosRecordatorios as $c)
                        <tr class="hover:bg-ink-50">
                            <td class="font-medium text-ink-900">@if(!empty($c['fecha_larga'])){{ $c['fecha_larga'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-ink-600 font-medium capitalize">@if(!empty($c['dia'])){{ $c['dia'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="font-medium text-ink-900 capitalize">@if(!empty($c['nombre'])){{ $c['nombre'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-ink-500 capitalize">@if(!empty($c['parentesco'])){{ $c['parentesco'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-center">
                                <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.035-.586 1.414L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    @if(!empty($c['recordatorio_hora'])){{ substr($c['recordatorio_hora'], 0, 5) }}@else<span class="text-ink-300">—</span>@endif
                                </span>
                            </td>
                            <td class="text-ink-500 max-w-[250px] truncate capitalize">@if(!empty($c['detalles'])){{ $c['detalles'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $c['proximidad'] <= 7 ? 'bg-blue-100 text-blue-700' : 'bg-ink-100 text-ink-700' }}">
                                    {{ $c['proximidad'] }} día{{ $c['proximidad'] !== 1 ? 's' : '' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    @if(empty($cumpleanosHoy))
    <div class="card">
        <div class="card-body py-16 text-center text-ink-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-ink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p>No hay recordatorios de cumpleaños configurados.</p>
            <p class="text-sm mt-1">Ve a la sección <strong>Cumpleaños</strong> y activa el recordatorio en algún registro.</p>
        </div>
    </div>
    @endif
    @endif
</div>

@push('scripts')
<script>
function recordatoriosComponent() {
    return {
        testNotification() {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: '🎂 ¡Recordatorio de prueba! Hoy cumple: Juan Pérez (HIJO)', type: 'success' }
            }));
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🎂 ¡Recordatorio de Cumpleaños!', {
                    body: 'Hoy cumple: Juan Pérez (HIJO)',
                    icon: '/favicon.ico',
                });
            } else if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(perm => {
                    if (perm === 'granted') {
                        new Notification('🎂 ¡Recordatorio de Cumpleaños!', {
                            body: 'Hoy cumple: Juan Pérez (HIJO)',
                            icon: '/favicon.ico',
                        });
                    }
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
        }
    };
}
</script>
@endpush
