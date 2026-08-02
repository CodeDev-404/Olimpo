<div>
    <div class="card mb-5" style="overflow:visible">
        <div class="card-body">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-[12.8rem] sm:min-w-[12.8rem]">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400"></i>
                <input type="text" wire:model.live="search" placeholder="Buscar..."
                    class="input-field pl-9 h-9 w-full" />
            </div>
        </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-1.5">
                <span class="text-xs text-ink-500 dark:text-ink-400 font-medium flex items-center gap-1">
                    @if($search)
                        Resultados para '{{ $search }}'
                        <span class="text-ink-300">({{ count($this->registros) }})</span>
                    @else
                        Registro PALM
                        <span class="text-ink-300">({{ count($this->registros) }})</span>
                    @endif
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-400">Hoy {{ $this->registrosHoy }}</span>
                    <span class="text-ink-300 mx-0.5">·</span>
                    <span class="text-ink-600 dark:text-ink-300 font-semibold">Total {{ count($this->registros) }}</span>
                </span>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($selectMode)
                        <span class="text-xs text-ink-400 font-medium whitespace-nowrap">{{ count($selectedIds) }} seleccionados</span>
                    @endif

                    <button wire:click="toggleSelectMode" class="btn btn-sm {{ $selectMode ? 'btn-warning' : 'btn-outline' }}">
                        @if($selectMode)
                            <i data-lucide="circle-x" class="w-4 h-4 mr-1.5 shrink-0"></i>
                            Cancelar
                        @else
                            <i data-lucide="check-square" class="w-4 h-4 mr-1.5 shrink-0"></i>
                            Seleccionar
                        @endif
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table-adminlte">
                        <thead>
                            <tr>
                                @if($selectMode)
                                <th class="w-10">
                                    <input type="checkbox" wire:click="toggleSelectAll"
                                        wire:key="select-all-{{ count($selectedIds) > 0 && count($this->registros) > 0 && count($selectedIds) === count($this->registros) ? 'on' : 'off' }}"
                                        {{ count($selectedIds) > 0 && count($this->registros) > 0 && count($selectedIds) === count($this->registros) ? 'checked' : '' }}>
                                </th>
                                @endif
                                <th class="hidden sm:table-cell">#</th>
                                <th>Fecha</th>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->registros as $i => $r)
                            <tr class="whitespace-nowrap {{ $selectMode && in_array($r['id'], $selectedIds) ? 'bg-ink-50 dark:bg-ink-800' : ($i % 2 === 1 ? 'table-row-zebra' : '') }}">
                                @if($selectMode)
                                <td class="w-10" wire:click.stop="toggleSelect({{ $r['id'] }})">
                                    <input type="checkbox" wire:key="row-{{ $r['id'] }}-{{ in_array($r['id'], $selectedIds) ? 'on' : 'off' }}"
                                        {{ in_array($r['id'], $selectedIds) ? 'checked' : '' }}>
                                </td>
                                @endif
                                <td class="hidden sm:table-cell font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                                <td class="font-medium">{!! h($r['fecha'] ?? null, $search) !!}</td>
                                <td class="font-medium text-ink-900 dark:text-ink-100">{!! h($r['nombre'] ?? null, $search) !!}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $selectMode ? 4 : 3 }}" class="px-3 py-16 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i data-lucide="file-text" class="w-8 h-8 text-ink-300 dark:text-white/20"></i>
                                        </div>
                                        <p class="empty-state-title">Panel de Registro PALM</p>
                                        <p class="empty-state-desc">El módulo estará disponible próximamente. Los registros aparecerán aquí.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
