<div>
    @php $greeting = \App\Helpers\GreetingHelper::getGreeting(auth()->id()); @endphp

    {{-- Greeting Banner --}}
    <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06] p-3 sm:p-5 mb-4 sm:mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-4">
        <div class="flex items-start gap-2 sm:gap-4 w-full sm:w-auto">
            <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-[#5D87FF]/10 flex items-center justify-center shrink-0">
                <i data-lucide="sun" class="w-4 h-4 sm:w-6 sm:h-6 text-[#5D87FF]"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-sm sm:text-lg font-bold text-ink-900 dark:text-white truncate">&iexcl;Hola, {{ auth()->user()->name }}! {{ $greeting['saludo'] }}.</h2>
                <p class="text-xs sm:text-sm text-ink-500 dark:text-white/60 mt-0.5">Que tengas un excelente servicio hoy!</p>
                <div class="hidden sm:flex items-center gap-3 mt-2 sm:mt-3 text-xs text-ink-400 dark:text-white/50">
                    <span class="inline-flex items-center gap-1.5">
                        <i data-lucide="quote" class="w-3 h-3"></i>
                        &ldquo;{{ $greeting['frase'] }}&rdquo;
                    </span>
                </div>
            </div>
        </div>
        <div class="flex gap-2 shrink-0 self-end sm:self-auto">
            <a href="{{ route('olimpo.ocurrencias') }}" class="quick-action quick-action-primary text-xs">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Nueva Ocurrencia</span>
                <span class="sm:hidden">Nueva</span>
            </a>
        </div>
    </div>

    {{-- Birthday Alert --}}
    @if(count($cumpleanos) > 0)
    <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06] p-3 sm:p-5 mb-4 sm:mb-6">
        <div class="flex items-start gap-2 sm:gap-4">
            <div class="w-6 h-6 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="cake" class="w-3 h-3 sm:w-5 sm:h-5 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-[10px] sm:text-sm font-bold text-amber-600 dark:text-amber-300 uppercase tracking-wider mb-1.5 sm:mb-3 font-display">¡Cumpleaños de hoy!</h3>
                <div class="space-y-1.5 sm:space-y-2">
                    @foreach($cumpleanos as $c)
                    <div class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 bg-amber-50 dark:bg-amber-500/5 rounded-xl border border-amber-200/40 dark:border-amber-500/10">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="party-popper" class="w-3 h-3 sm:w-4 sm:h-4 text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:font-semibold text-ink-900 dark:text-white font-display truncate">{{ $c['nombre'] }}</p>
                            @if($c['parentesco'])
                                <p class="text-[10px] sm:text-xs text-ink-500 dark:text-white/50 capitalize truncate">{{ $c['parentesco'] }}</p>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 sm:px-3 sm:py-1 bg-amber-200/80 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300 text-[9px] sm:text-[10px] font-bold rounded-lg uppercase tracking-wider font-label shrink-0">HOY</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- KPI Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-4 sm:mb-6">
        <div class="kpi-card bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06] p-3 sm:p-5">
            <div class="flex items-start justify-between mb-2 sm:mb-4">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-lg bg-gradient-to-br from-[#5D87FF] to-[#49BEFF] flex items-center justify-center">
                    <i data-lucide="users" class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <span class="inline-flex items-center gap-0.5 text-[10px] sm:text-[11px] font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/15 px-1 sm:px-1.5 py-0.5 rounded-lg">↑ {{ $kpis['personal_pct'] }}%</span>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-ink-900 dark:text-white">{{ $kpis['personal_activo'] }}<span class="text-xs sm:text-base font-normal text-ink-400 mx-0.5">/</span><span class="text-xs sm:text-base font-normal text-ink-400">{{ $kpis['personal_total'] }}</span></p>
            <p class="text-[11px] sm:text-sm text-ink-500 dark:text-white/60 mt-0.5 sm:mt-1">Personal Activo</p>
            <div class="flex items-center gap-1 mt-2 sm:mt-3">
                <div class="flex-1 h-1 sm:h-1.5 rounded-full bg-ink-100 dark:bg-white/[0.06]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#5D87FF] to-[#49BEFF]" style="width:{{ $kpis['personal_total'] > 0 ? ($kpis['personal_activo'] / $kpis['personal_total'] * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
        <div class="kpi-card bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06] p-3 sm:p-5">
            <div class="flex items-start justify-between mb-2 sm:mb-4">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-lg bg-gradient-to-br from-[#13DEB9] to-[#49BEFF] flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <span class="inline-flex items-center gap-0.5 text-[10px] sm:text-[11px] font-semibold text-ink-400 bg-ink-100 dark:bg-white/[0.06] px-1 sm:px-1.5 py-0.5 rounded-lg">{{ $kpis['ocurrencias_hoy'] }} hoy</span>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-ink-900 dark:text-white">{{ $kpis['ocurrencias_mes'] }}</p>
            <p class="text-[11px] sm:text-sm text-ink-500 dark:text-white/60 mt-0.5 sm:mt-1">Ocurrencias del Mes</p>
            <div class="flex items-center gap-1 mt-2 sm:mt-3">
                <div class="flex-1 h-1 sm:h-1.5 rounded-full bg-ink-100 dark:bg-white/[0.06]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#13DEB9] to-[#49BEFF]" style="width:{{ $kpis['ocurrencias_mes'] > 0 ? min(100, $kpis['ocurrencias_mes'] / 200 * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
        <div class="kpi-card bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06] p-3 sm:p-5">
            <div class="flex items-start justify-between mb-2 sm:mb-4">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-lg bg-gradient-to-br from-[#FFAE1F] to-[#FA896B] flex items-center justify-center">
                    <i data-lucide="truck" class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <span class="inline-flex items-center gap-0.5 text-[10px] sm:text-[11px] font-semibold text-ink-400 bg-ink-100 dark:bg-white/[0.06] px-1 sm:px-1.5 py-0.5 rounded-lg">{{ $kpis['vehiculos_uso'] }} activos</span>
            </div>
            <p class="text-lg sm:text-2xl font-bold text-ink-900 dark:text-white">{{ $kpis['vehiculos_total'] }}</p>
            <p class="text-[11px] sm:text-sm text-ink-500 dark:text-white/60 mt-0.5 sm:mt-1">Vehículos Registrados</p>
            <div class="flex items-center gap-1 sm:gap-1.5 mt-2 sm:mt-3">
                <div class="flex-1 h-1 sm:h-1.5 rounded-full bg-ink-100 dark:bg-white/[0.06]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#FFAE1F] to-[#FA896B]" style="width:{{ $kpis['vehiculos_total'] > 0 ? ($kpis['vehiculos_uso'] / $kpis['vehiculos_total'] * 100) : 0 }}%"></div>
                </div>
                <span class="text-[10px] sm:text-xs font-semibold text-ink-500 dark:text-white/60">{{ $kpis['vehiculos_total'] > 0 ? round($kpis['vehiculos_uso'] / $kpis['vehiculos_total'] * 100) : 0 }}%</span>
            </div>
        </div>
        <div class="kpi-card bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06] p-3 sm:p-5">
            <div class="flex items-start justify-between mb-2 sm:mb-4">
                <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-lg bg-gradient-to-br from-[#539BFF] to-[#5D87FF] flex items-center justify-center">
                    <i data-lucide="fuel" class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-white"></i>
                </div>
                <span class="inline-flex items-center gap-0.5 text-[10px] sm:text-[11px] font-semibold text-ink-400 bg-ink-100 dark:bg-white/[0.06] px-1 sm:px-1.5 py-0.5 rounded-lg">S/ {{ number_format($kpis['combustible_mes'], 2) }}</span>
            </div>
            <p class="text-base sm:text-2xl font-bold text-ink-900 dark:text-white sm:truncate">S/ {{ number_format($kpis['combustible_mes'], 2) }}</p>
            <p class="text-[11px] sm:text-sm text-ink-500 dark:text-white/60 mt-0.5 sm:mt-1">Combustible del Mes</p>
            <div class="flex items-center gap-1 sm:gap-1.5 mt-2 sm:mt-3">
                <div class="flex-1 h-1 sm:h-1.5 rounded-full bg-ink-100 dark:bg-white/[0.06]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#539BFF] to-[#5D87FF]" style="width:{{ $kpis['vehiculos_total'] > 0 ? min(100, $kpis['vehiculos_uso'] / $kpis['vehiculos_total'] * 100) : 0 }}%"></div>
                </div>
                <span class="text-[10px] sm:text-xs font-semibold text-ink-500 dark:text-white/60">{{ $kpis['vehiculos_total'] > 0 ? round($kpis['vehiculos_uso'] / $kpis['vehiculos_total'] * 100) : 0 }}%</span>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('olimpo.ocurrencias') }}" class="quick-action quick-action-primary text-xs">
            <i data-lucide="plus-circle" class="w-3.5 h-3.5 shrink-0"></i>
            <span class="hidden sm:inline">Nueva ocurrencia</span>
            <span class="sm:hidden">Ocurrencia</span>
        </a>
        <a href="{{ route('olimpo.personal') }}" class="quick-action quick-action-success text-xs">
            <i data-lucide="user-plus" class="w-3.5 h-3.5 shrink-0"></i>
            <span class="hidden sm:inline">Registrar personal</span>
            <span class="sm:hidden">Personal</span>
        </a>
        <a href="{{ route('olimpo.asistencia') }}" class="quick-action quick-action-warning text-xs">
            <i data-lucide="calendar" class="w-3.5 h-3.5 shrink-0"></i>
            <span class="hidden sm:inline">Tomar asistencia</span>
            <span class="sm:hidden">Asistencia</span>
        </a>
        <a href="{{ route('olimpo.control-vehiculos') }}" class="quick-action quick-action-danger text-xs">
            <i data-lucide="truck" class="w-3.5 h-3.5 shrink-0"></i>
            <span class="hidden sm:inline">Registrar vehículo</span>
            <span class="sm:hidden">Vehículo</span>
        </a>
    </div>

    {{-- Main 2-col Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-5 mb-4 sm:mb-6">
        {{-- Activity Timeline --}}
        <div class="lg:col-span-2 bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-3 sm:px-5 py-2.5 sm:py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                <h3 class="text-xs sm:text-sm font-bold text-ink-900 dark:text-white flex items-center gap-1.5 sm:gap-2">
                    <i data-lucide="activity" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#5D87FF]"></i>Actividad Reciente
                </h3>
                <a href="{{ route('olimpo.ocurrencias') }}" class="text-[10px] sm:text-xs font-semibold text-[#5D87FF]">Ver todo →</a>
            </div>
            <div class="p-3 sm:p-5">
                @php $items = count($ocurrenciasHoy) > 0 ? $ocurrenciasHoy : $ocurrenciasRecientes; @endphp
                @if(count($items) > 0)
                    @foreach(array_slice($items, 0, 6) as $o)
                    <div class="flex items-start gap-3 py-3 {{ !$loop->first ? 'border-t border-[#e5eaef] dark:border-white/[0.06]' : '' }}">
                        <div class="w-9 h-9 rounded-lg bg-[#5D87FF]/10 text-[#5D87FF] flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($o['persona'], 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-ink-900 dark:text-white truncate">{{ $o['persona'] }}</div>
                            <div class="text-xs text-ink-500 dark:text-white/50 mt-0.5">{{ $o['tipo'] }} @if($o['vehiculo'])· {{ $o['vehiculo'] }} @endif @if($o['destino'])→ {{ $o['destino'] }} @endif</div>
                            <div class="text-[10px] text-ink-400 dark:text-white/40 mt-0.5">{{ $o['hora_ingreso'] ?? $o['hora_salida'] ?? '—' }} @if(isset($o['fecha'])) · {{ $o['fecha'] }} @endif</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider shrink-0 self-start {{ $o['tipo'] === 'INGRESO' || $o['tipo'] === 'ENTRADA' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : ($o['tipo'] === 'SALIDA' ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' : 'bg-blue-50 text-[#539BFF] dark:bg-blue-500/10 dark:text-blue-400') }}">
                            {{ $o['tipo'] === 'INGRESO' || $o['tipo'] === 'ENTRADA' ? 'Entrada' : ($o['tipo'] === 'SALIDA' ? 'Salida' : $o['tipo']) }}
                        </span>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-ink-400 dark:text-white/40 text-center py-8">Sin actividad registrada</p>
                @endif
            </div>
        </div>

        {{-- Right column --}}
        <div class="flex flex-col gap-5">
            {{-- Attendance --}}
            <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
                <div class="flex items-center justify-between px-5 py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                    <h3 class="text-sm font-bold text-ink-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="calendar-check" class="w-4 h-4 text-[#13DEB9]"></i>Asistencia Hoy
                    </h3>
                    <a href="{{ route('olimpo.asistencia') }}" class="text-xs font-semibold text-[#5D87FF]">Ver →</a>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-emerald-500">{{ $kpis['asistencia_hoy'] }}</p>
                            <p class="text-xs text-ink-500 dark:text-white/60 mt-1">Presentes</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-amber-500">{{ $kpis['tardanzas_hoy'] }}</p>
                            <p class="text-xs text-ink-500 dark:text-white/60 mt-1">Tardanzas</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-500">{{ max(0, $kpis['ausentes_hoy']) }}</p>
                            <p class="text-xs text-ink-500 dark:text-white/60 mt-1">Ausentes</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-[#5D87FF]">{{ $kpis['personal_total'] }}</p>
                            <p class="text-xs text-ink-500 dark:text-white/60 mt-1">Total</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
                <div class="flex items-center justify-between px-5 py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                    <h3 class="text-sm font-bold text-ink-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="bell" class="w-4 h-4 text-[#FFAE1F]"></i>Notificaciones
                    </h3>
                    <span class="text-[11px] font-semibold text-ink-400 dark:text-white/50">{{ count($notificaciones) }} pendientes</span>
                </div>
                <div class="p-5">
                    @if(count($notificaciones) > 0)
                        @foreach($notificaciones as $n)
                        <div class="flex items-start gap-3 py-2.5 {{ !$loop->first ? 'border-t border-[#e5eaef] dark:border-white/[0.06]' : '!pt-0' }}">
                            <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $n['tipo'] === 'danger' ? 'bg-red-500' : ($n['tipo'] === 'warning' ? 'bg-amber-500' : 'bg-[#539BFF]') }}"></span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-ink-900 dark:text-white">{{ $n['titulo'] }}</div>
                                <div class="text-xs text-ink-500 dark:text-white/50 mt-0.5">{{ $n['desc'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-ink-400 dark:text-white/40 text-center py-4">Sin notificaciones</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom 3-col Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        {{-- Calendar Widget --}}
        <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                <h3 class="text-sm font-bold text-ink-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-[#5D87FF]"></i>{{ now()->isoFormat('MMMM YYYY') }}
                </h3>
                <span class="text-[11px] text-ink-400 dark:text-white/50 font-label">{{ now()->isoFormat('dddd') }}</span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-7 mb-1">
                    <span class="text-center text-[10px] font-semibold text-ink-400 dark:text-white/50 py-1">Do</span>
                    <span class="text-center text-[10px] font-semibold text-ink-400 dark:text-white/50 py-1">Lu</span>
                    <span class="text-center text-[10px] font-semibold text-ink-400 dark:text-white/50 py-1">Ma</span>
                    <span class="text-center text-[10px] font-semibold text-ink-400 dark:text-white/50 py-1">Mi</span>
                    <span class="text-center text-[10px] font-semibold text-ink-400 dark:text-white/50 py-1">Ju</span>
                    <span class="text-center text-[10px] font-semibold text-ink-400 dark:text-white/50 py-1">Vi</span>
                    <span class="text-center text-[10px] font-semibold text-ink-400 dark:text-white/50 py-1">Sá</span>
                </div>
                <div class="grid grid-cols-7">
                    @foreach($calendarioMes as $dia)
                        @if($dia === null)
                            <div class="text-center py-2 text-[11px] text-ink-200 dark:text-white/10"></div>
                        @else
                            @php
                                $hoy = now()->day;
                                $isToday = $dia === $hoy;
                                $eventos = $calendarioEventos[$dia] ?? [];
                            @endphp
                            <div class="text-center py-2 text-[11px] {{ $isToday ? 'bg-[#5D87FF] text-white rounded-lg font-bold' : 'text-ink-700 dark:text-white/70' }}" title="{{ $dia }}/{{ now()->format('m') }}">
                                <span>{{ $dia }}</span>
                                @if(count($eventos) > 0)
                                <div class="flex items-center justify-center gap-0.5 mt-0.5">
                                    @foreach($eventos as $ev)
                                        <span class="w-1 h-1 rounded-full {{ $ev['type'] === 'ocurrencia' ? 'bg-[#5D87FF]' : ($ev['type'] === 'asistencia' ? 'bg-[#13DEB9]' : 'bg-[#FFAE1F]') }}"></span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="flex items-center gap-4 mt-3 text-[10px] text-ink-400 dark:text-white/50">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#5D87FF]"></span>Ocurrencias</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#13DEB9]"></span>Asistencia</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#FFAE1F]"></span>Cumpleaños</span>
                </div>
            </div>
        </div>

        {{-- Occurrences by Type --}}
        <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                <h3 class="text-sm font-bold text-ink-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-[#13DEB9]"></i>Ocurrencias x Tipo
                </h3>
            </div>
            <div class="p-5">
                @if($chartTipoData)
                    @foreach($chartTipoData['labels'] as $i => $label)
                    <div class="flex items-center justify-between py-2 {{ !$loop->first ? 'border-t border-[#e5eaef] dark:border-white/[0.06]' : '' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $chartTipoData['colors'][$i] ?? '#5D87FF' }}"></span>
                            <span class="text-sm text-ink-700 dark:text-white/70">{{ $label }}</span>
                        </div>
                        <span class="text-sm font-semibold text-ink-900 dark:text-white">{{ $chartTipoData['data'][$i] ?? 0 }}</span>
                    </div>
                    @endforeach
                    <div class="flex items-center justify-between py-2 mt-2 border-t-2 border-[#e5eaef] dark:border-white/[0.06]">
                        <span class="text-sm font-bold text-ink-900 dark:text-white">Total</span>
                        <span class="text-sm font-bold text-[#5D87FF]">{{ array_sum($chartTipoData['data']) }}</span>
                    </div>
                @else
                    <p class="text-sm text-ink-400 dark:text-white/40 text-center py-6">Sin datos este mes</p>
                @endif
            </div>
        </div>

        {{-- Personnel Online --}}
        <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                <h3 class="text-sm font-bold text-ink-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-[#5D87FF]"></i>Personal en Línea
                </h3>
                <span class="text-[11px] text-ink-400 dark:text-white/50 font-semibold">
                    {{ collect($personalOnline)->where('online', true)->count() }} activos
                </span>
            </div>
            <div class="p-5">
                @if(count($personalOnline) > 0)
                    @foreach(array_slice($personalOnline, 0, 6) as $s)
                    <div class="flex items-center gap-3 py-2 {{ !$loop->first ? 'border-t border-[#e5eaef] dark:border-white/[0.06]' : '' }}">
                        <span class="w-2 h-2 rounded-full {{ $s['online'] ? 'bg-emerald-500' : 'bg-ink-300 dark:bg-white/20' }}"></span>
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-semibold {{ $s['online'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-ink-400 dark:text-white/50' }}">
                                {{ $s['name'] }}
                                @if($s['status'])
                                <span class="text-[10px] text-ink-400 dark:text-white/50 font-normal ml-1">{{ $s['status'] }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-ink-400 dark:text-white/40 text-center py-6">Sin personal activo</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                <h3 class="text-sm font-bold text-ink-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-[#5D87FF]"></i>Ocurrencias por Tipo
                </h3>
            </div>
            <div class="p-5">
                @if($chartTipoData)
                <div style="height: 220px; max-width: 320px; margin: 0 auto;">
                    <canvas
                        x-data="{
                            init() {
                                new Chart(this.$el, {
                                    type: 'doughnut',
                                    data: {
                                        labels: @js($chartTipoData['labels']),
                                        datasets: [{
                                            data: @js($chartTipoData['data']),
                                            backgroundColor: @js($chartTipoData['colors']),
                                            borderWidth: 3,
                                            borderColor: document.documentElement.classList.contains('dark') ? '#1C1F2E' : '#fff',
                                            hoverOffset: 8
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        cutout: '72%',
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: { color: '#64748b', font: { size: 11, family: 'DM Sans' }, boxWidth: 12, padding: 12, usePointStyle: true }
                                            }
                                        }
                                    }
                                });
                            }
                        }"
                    ></canvas>
                </div>
                @else
                <div class="py-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-[#f4f6f9] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="chart-pie" class="w-6 h-6 text-ink-400 dark:text-white/40"></i>
                    </div>
                    <p class="text-sm font-medium text-ink-500 dark:text-white/50">Sin datos este mes</p>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-[#111a30]/70 rounded-xl border border-[#e5eaef] dark:border-white/[0.06]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#e5eaef] dark:border-white/[0.06]">
                <h3 class="text-sm font-bold text-ink-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-[#5D87FF]"></i>Tendencia Semanal
                </h3>
            </div>
            <div class="p-5">
                <div style="height: 220px;">
                    <canvas
                        x-data="{
                            init() {
                                new Chart(this.$el, {
                                    type: 'bar',
                                    data: {
                                        labels: @js(collect($chartSemanalData)->pluck('label')),
                                        datasets: [{
                                            label: 'Ocurrencias',
                                            data: @js(collect($chartSemanalData)->pluck('total')),
                                            backgroundColor: ['#5D87FF', '#49BEFF', '#5D87FF', '#49BEFF', '#5D87FF', '#49BEFF', '#5D87FF'],
                                            borderRadius: 6,
                                            borderSkipped: false
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } },
                                        scales: {
                                            y: {
                                                ticks: { color: '#94a3b8', stepSize: 1, font: { size: 10 } },
                                                grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,.03)' : '#f1f5f9', drawTicks: false }
                                            },
                                            x: {
                                                ticks: { color: '#94a3b8', font: { size: 9, family: 'DM Sans' } },
                                                grid: { display: false }
                                            }
                                        }
                                    }
                                });
                            }
                        }"
                    ></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('refreshDashboard', () => {
                Livewire.dispatch('$refresh');
            });
        });
    </script>
    @endpush
</div>
