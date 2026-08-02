<div>
    @if($show)
    <div class="modal-overlay"
        x-data x-show="$wire.show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="modal-card w-full max-w-4xl mx-4"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="modal-header">
                <div>
                    <h3 class="text-base font-semibold text-ink-900 dark:text-ink-100 font-display">Importar {{ match($panel) { 'ocurrencias' => 'Ocurrencias', 'personal' => 'Personal', 'asistencia' => 'Asistencia', 'cumpleanos' => 'Cumpleaños', 'control_vehiculos' => 'Control Vehículos', 'combustibles' => 'Combustibles', default => $panel } }}</h3>
                    <p class="text-[11px] text-ink-400 mt-0.5">Formatos aceptados: .xlsx, .xls, .csv</p>
                </div>
                <button wire:click="close" class="p-1 text-ink-400 hover:text-ink-700 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                @if(empty($rows) && !$importing)
                    <div class="border-2 border-dashed border-ink-200 dark:border-ink-600 rounded-xl p-10 text-center hover:border-ink-400 transition-colors cursor-pointer"
                        onclick="document.getElementById('import-file').click()">
                        <svg class="w-10 h-10 mx-auto mb-3 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm text-ink-500 dark:text-ink-400 font-medium">Haz clic para seleccionar archivo</p>
                        <p class="text-xs text-ink-400 mt-1">o arrastra y suelta aquí</p>
                    </div>
                    <input id="import-file" type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="hidden">
                    @error('file') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                @elseif($importing)
                    <div class="text-center py-10">
                        <svg class="w-8 h-8 mx-auto mb-3 text-ink-500 dark:text-ink-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <p class="text-sm text-ink-500 dark:text-ink-400">Procesando archivo...</p>
                    </div>
                @else
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-ink-600 dark:text-ink-400">
                            {{ count($rows) }} fila(s) encontradas —
                            <span class="text-green-600 font-medium">{{ collect($rows)->where('valid', true)->count() }} válidas</span>
                            <span class="text-red-500"> / {{ collect($rows)->where('valid', false)->count() }} con errores</span>
                        </span>
                    </div>

                    <div class="overflow-x-auto border border-ink-200 dark:border-ink-600 rounded-lg max-h-64 overflow-y-auto">
                        <table class="table-adminlte">
                            <thead>
                                <tr>
                                    <th class="w-10">
                                        <input type="checkbox"
                                            wire:click="{{ count($this->selectedRows) === collect($this->rows)->where('valid', true)->count() ? 'deselectAll' : 'selectAll' }}"
                                            @checked(count($this->selectedRows) === collect($this->rows)->where('valid', true)->count() && count($this->rows) > 0)>
                                    </th>
                                    <th>#</th>
                                    @if($panel === 'ocurrencias')
                                        <th>Fecha</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Detalles</th>
                                    @elseif($panel === 'personal')
                                        <th>Nombre</th>
                                        <th>Cargo</th>
                                        <th>Documento</th>
                                        <th>Estado</th>
                    @elseif($panel === 'cumpleanos')
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Detalles</th>
                    @elseif($panel === 'control_vehiculos')
                        <th>Fecha</th>
                        <th>Chofer</th>
                        <th>Placa</th>
                        <th>Marca</th>
                    @elseif($panel === 'combustibles')
                        <th>Fecha</th>
                        <th>Combustible</th>
                        <th>Placa</th>
                        <th>Galones</th>
                    @else
                        <th>Nombre</th>
                        <th>Fecha</th>
                        <th>H. Entrada</th>
                        <th>Turno</th>
                        <th>Etiqueta</th>
                    @endif
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $i => $row)
                                    <tr class="{{ !$row['valid'] ? 'bg-red-50' : ($i % 2 === 1 ? 'table-row-zebra' : '') }}">
                                        <td>
                                            @if($row['valid'])
                                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $i }}"
                                                    class="rounded border-ink-300 text-ink-800 dark:text-ink-200">
                                            @endif
                                        </td>
                                        <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                                        @if($panel === 'ocurrencias')
                                            <td>{{ $row['preview']['fecha'] }}</td>
                                            <td class="font-medium">{{ $row['preview']['nombre'] }}</td>
                                            <td>{{ $row['preview']['tipo'] }}</td>
                                            <td class="text-ink-500 dark:text-ink-400 text-xs max-w-[150px] truncate">{{ $row['preview']['detalles'] }}</td>
                                        @elseif($panel === 'personal')
                                            <td class="font-medium">{{ $row['preview']['nombre'] }}</td>
                                            <td>{{ $row['preview']['cargo'] }}</td>
                                            <td>{{ $row['preview']['documento'] }}</td>
                                            <td>
                                                <span class="badge {{ $row['preview']['estado'] === 'ACTIVO' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ $row['preview']['estado'] }}
                                                </span>
                                            </td>
                                        @elseif($panel === 'cumpleanos')
                                            <td>{{ $row['preview']['fecha'] }}</td>
                                            <td class="font-medium">{{ $row['preview']['nombre'] }}</td>
                                            <td class="text-ink-500 dark:text-ink-400 text-xs max-w-[150px] truncate">{{ $row['preview']['detalles'] }}</td>
                                        @elseif($panel === 'control_vehiculos')
                                            <td>{{ $row['preview']['fecha'] }}</td>
                                            <td class="font-medium">{{ $row['preview']['chofer'] }}</td>
                                            <td>{{ $row['preview']['placa'] }}</td>
                                            <td>{{ $row['preview']['marca'] }}</td>
                                        @elseif($panel === 'combustibles')
                                            <td>{{ $row['preview']['fecha'] }}</td>
                                            <td class="font-medium">{{ $row['preview']['combustible'] }}</td>
                                            <td>{{ $row['preview']['placa'] }}</td>
                                            <td>{{ $row['preview']['galones'] }}</td>
                                        @else
                                            <td class="font-medium">{{ $row['preview']['nombre'] }}</td>
                                            <td>{{ $row['preview']['fecha'] }}</td>
                                            <td>{{ $row['preview']['hora_entrada'] }}</td>
                                            <td>
                                                <span class="text-xs font-semibold {{ $row['preview']['turno'] === 'NOCHE' ? 'text-ink-500 dark:text-ink-400' : ($row['preview']['turno'] === '24H' ? 'text-purple-600' : 'text-blue-600') }}">
                                                    {{ $row['preview']['turno'] }}
                                                </span>
                                            </td>
                                            <td>{{ $row['preview']['etiqueta'] }}</td>
                                        @endif
                                        <td>
                                            @if($row['valid'])
                                                <span class="text-green-600 text-xs font-medium">✓</span>
                                            @else
                                                <span class="text-red-500 text-xs" title="{{ implode('; ', $row['errors']) }}">✗ {{ $row['errors'][0] ?? '' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 mt-3">
                        <button wire:click="$set('rows', [])" class="btn btn-outline btn-sm">Volver a elegir archivo</button>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="close" class="btn btn-secondary">Cancelar</button>
                            <button wire:click="confirm" class="btn btn-primary"
                                wire:loading.attr="disabled"
                                wire:target="confirm">
                                <span wire:loading.remove wire:target="confirm">Importar {{ count($this->selectedRows) }} registro(s)</span>
                                <span wire:loading wire:target="confirm">Importando...</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
