<div x-data="recordatoriosComponent()" x-cloak>
    <div class="flex items-center justify-between mb-5">
        <div class="flex gap-1 bg-[#f4f6f9] dark:bg-white/[0.06] rounded-lg p-0.5" role="tablist">
            <button @click="tab = 'cumpleanos'" :class="tab === 'cumpleanos' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab" aria-selected="true">Cumpleaños</button>
            <button @click="tab = 'otros-pendientes'" :class="tab === 'otros-pendientes' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab" aria-selected="false">Otros Pendientes</button>
        </div>
        <button @click="testNotification()" class="btn btn-info btn-sm">
            <i data-lucide="bell" class="w-4 h-4 mr-1"></i>
            Probar Notificación
        </button>
    </div>

    <div x-show="tab === 'cumpleanos'">

    @if(!empty($cumpleanosHoy))
    <div class="mb-6 rounded-xl border border-[#FFAE1F]/30 bg-amber-50 dark:bg-amber-500/5 p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0"><i data-lucide="cake" class="w-8 h-8 text-ember-500"></i></div>
            <div class="flex-1">
                    <h3 class="text-sm font-bold text-ember-800 uppercase tracking-wider mb-2 font-display">
                    ¡Cumpleaños de hoy con recordatorio activo!
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
                                Recordatorio a las {{ substr($c['recordatorio_hora'], 0, 5) }}
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

    @if(!empty($proximosRecordatorios))
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-ink-900 dark:text-ink-100 font-display">Próximos cumpleaños con recordatorio</h3>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            <th>#</th>
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
                        @foreach($proximosRecordatorios as $i => $c)
                        <tr class="hover:bg-ink-50 dark:hover:bg-ink-800">
                            <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                            <td class="font-medium text-ink-900 dark:text-ink-100">@if(!empty($c['fecha_larga'])){{ $c['fecha_larga'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-ink-600 dark:text-ink-400 font-medium capitalize">@if(!empty($c['dia'])){{ $c['dia'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="font-medium text-ink-900 dark:text-ink-100 capitalize">@if(!empty($c['nombre'])){{ $c['nombre'] }}@else<span class="text-ink-300">—</span>@endif
                                @if(!empty($c['alias'])) <span class="text-ink-400 text-xs font-normal">({{ $c['alias'] }})</span> @endif
                            </td>
                            <td class="text-ink-500 dark:text-ink-400 capitalize">@if(!empty($c['parentesco'])){{ $c['parentesco'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-center">
                                <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                                    <i data-lucide="bell" class="w-4 h-4"></i>
                                    @if(!empty($c['recordatorio_hora'])){{ substr($c['recordatorio_hora'], 0, 5) }}@else<span class="text-ink-300">—</span>@endif
                                </span>
                            </td>
                            <td class="text-ink-500 dark:text-ink-400 max-w-[250px] truncate capitalize">@if(!empty($c['detalles'])){{ $c['detalles'] }}@else<span class="text-ink-300">—</span>@endif</td>
                            <td class="text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $c['proximidad'] <= 7 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-ink-100 text-ink-700 dark:text-ink-300' }}">
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
            <i data-lucide="bell" class="w-12 h-12 mx-auto mb-3 text-ink-200"></i>
            <p>No hay recordatorios de cumpleaños configurados.</p>
            <p class="text-sm mt-1">Ve a la sección <strong>Cumpleaños</strong> y activa el recordatorio en algún registro.</p>
        </div>
    </div>
    @endif
    @endif
    </div>

    <div x-show="tab === 'otros-pendientes'" class="card">
        <div class="card-body py-16 text-center text-ink-400">
            <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-ink-200"></i>
            <p>No hay pendientes registrados.</p>
            <p class="text-sm mt-1">Aquí podrás gestionar otras tareas y recordatorios.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function recordatoriosComponent() {
    return {
        tab: 'cumpleanos',
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
