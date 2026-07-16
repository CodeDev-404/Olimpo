<div>
    <div class="card mb-5">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider">Desde:</span>
                    <input type="text" wire:model="fechaDesde" class="input-field w-28" placeholder="DD/MM/AAAA">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider">Hasta:</span>
                    <input type="text" wire:model="fechaHasta" class="input-field w-28" placeholder="DD/MM/AAAA">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider">Tipo:</span>
                    <select wire:model="tipoFiltro" class="input-field w-36">
                        <option value="Todos">Todos</option>
                        @foreach($tipos as $t)
                            @if($t !== 'Todos')
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <div class="card border-l-[3px] border-l-red-500">
            <div class="card-body">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-red-700 text-sm">Ocurrencias → PDF</h3>
                        <p class="text-xs text-ink-400 mt-1">Reporte detallado con colores por tipo.</p>
                        <button wire:click="exportarOcurrenciasPDF" class="btn btn-danger btn-sm mt-3">Exportar PDF</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-l-[3px] border-l-blue-500">
            <div class="card-body">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-blue-700 text-sm">Ocurrencias → Excel</h3>
                        <p class="text-xs text-ink-400 mt-1">Tabla con filtros habilitados.</p>
                        <button wire:click="exportarOcurrenciasExcel" class="btn btn-primary btn-sm mt-3">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-l-[3px] border-l-green-500">
            <div class="card-body">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-green-700 text-sm">Asistencia → PDF</h3>
                        <p class="text-xs text-ink-400 mt-1">Reporte con etiquetas BUENO/REGULAR/MALO.</p>
                        <button wire:click="exportarAsistenciaPDF" class="btn btn-success btn-sm mt-3">Exportar PDF</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-l-[3px] border-l-ember-500">
            <div class="card-body">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-amber-700 text-sm">Asistencia → Excel</h3>
                        <p class="text-xs text-ink-400 mt-1">Planilla para análisis adicional.</p>
                        <button wire:click="exportarAsistenciaExcel" class="btn btn-warning btn-sm mt-3">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="font-semibold text-sm text-ink-900">Registro de Exportaciones</span>
        </div>
        <div class="card-body">
            <div class="bg-ink-50 border border-ink-200 rounded-lg p-4 h-32 overflow-y-auto text-sm text-ink-500 font-mono">
                @forelse($log as $entry)
                    <div class="py-0.5">{{ $entry }}</div>
                @empty
                    <div class="text-ink-400 italic">Aquí aparecerán las exportaciones...</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
