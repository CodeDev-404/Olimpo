@php
if (!function_exists('h')) {
    function h(?string $text, ?string $search = null): string {
        if ($text === null || $text === '') return '<span class="text-ink-300">—</span>';
        $text = e($text);
        if ($search) {
            foreach (explode(' ', $search) as $part) {
                $part = trim($part);
                if ($part === '') continue;
                $text = preg_replace('/(' . preg_quote($part, '/') . ')/iu', '<mark class="bg-yellow-200 text-ink-900 dark:text-ink-100 rounded px-0.5">$1</mark>', $text);
            }
        }
        return $text;
    }
}
@endphp
<div x-data="{ tab: 'bma828', filtersOpen: false, filtersOpenCombustible: false }" x-cloak>
    <div class="flex gap-1 bg-[#f4f6f9] dark:bg-white/[0.06] rounded-lg p-0.5 mb-5 w-fit" role="tablist">
        <button @click="tab = 'bma828'" :class="tab === 'bma828' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab">Movimientos</button>
        <button @click="tab = 'combustibles'" :class="tab === 'combustibles' ? 'bg-white dark:bg-[#1C1F2E] text-ink-900 dark:text-white shadow-sm' : 'text-ink-500 dark:text-white/60 hover:text-ink-700 dark:hover:text-white/80'" class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all" role="tab">Combustibles</button>
    </div>

    {{-- ==================== TAB: Control BMA-828 ==================== --}}
    <div x-show="tab === 'bma828'">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-[32rem] sm:min-w-[32rem]">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400"></i>
                    <input type="text" wire:model.live="search" placeholder="Buscar..."
                        class="input-field pl-9 h-9 w-full" />
                </div>

                <button
                    class="flex items-center gap-1.5 px-3 h-9 rounded-lg border border-[#e5eaef] dark:border-white/[0.06] bg-white dark:bg-ink-800/50 text-ink-400 dark:text-ink-500 hover:border-[#5D87FF] dark:hover:border-[#5D87FF] hover:text-[#5D87FF] dark:hover:text-[#5D87FF] transition-all duration-150 shrink-0"
                    :class="{ 'border-[#5D87FF] dark:border-[#5D87FF] text-[#5D87FF] dark:text-[#5D87FF] bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': filtersOpen }"
                    @click="
                        if(filtersOpen) {
                            $wire.set('search', '');
                            $wire.set('filterFecha', '');
                            $nextTick(() => filtersOpen = false);
                        } else {
                            filtersOpen = true;
                        }
                    "
                    :title="filtersOpen ? 'Cerrar y limpiar filtros' : 'Abrir filtros'">
                    <svg x-show="!filtersOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 6V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                    <span x-show="!filtersOpen" class="text-xs font-medium">Filtrar</span>
                    <svg x-show="filtersOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    <span x-show="filtersOpen" class="text-xs font-medium">Cerrar</span>
                </button>

                @php $afBma = ($search ? 1 : 0) + ($filterFecha ? 1 : 0); @endphp
                <div x-show="!filtersOpen && {{ $afBma > 0 ? 'true' : 'false' }}" x-cloak>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20 text-[#5D87FF] dark:text-[#5D87FF] border border-[#5D87FF]/20 dark:border-[#5D87FF]/40">
                        ● {{ $afBma }}
                    </span>
                </div>

                <div x-show="filtersOpen" class="flex items-center gap-3 flex-1 min-w-0 filter-slide-in">
                    <input type="date" wire:model.live="filterFecha" class="input-field h-9 w-56" title="Filtrar por fecha" />
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="nuevo" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                    Nuevo
                </button>
                <button wire:click="toggleSelectMode" class="btn btn-sm {{ $selectMode ? 'btn-warning' : 'btn-outline border-ink-200 dark:border-ink-600' }}">
                    {{ $selectMode ? 'Cancelar selección' : 'Seleccionar' }}
                </button>
                @if($selectMode)
                    <button wire:click="eliminarSeleccionados" wire:confirm="¿Eliminar los registros seleccionados?" class="btn btn-danger btn-sm">
                        Eliminar ({{ count($selectedIds) }})
                    </button>
                @endif
                <button wire:click="$dispatch('openImportModalFor', { panel: 'control_vehiculos' })" class="btn btn-outline btn-sm border-ink-200 dark:border-ink-600 text-ink-600 dark:text-ink-400 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                    <i data-lucide="upload" class="w-4 h-4 mr-1"></i>
                    Importar
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="text-xs text-ink-500 dark:text-ink-400 font-medium">
                    {{ $search ? "Resultados para '{$search}'" : 'Control de Vehículos' }}
                    <span class="text-ink-300">({{ $registros->total() }})</span>
                </span>
                @if($selectedId && !$selectMode)
                <div class="flex items-center gap-1">
                    <button wire:click="editar" class="btn btn-ghost btn-xs">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        Editar
                    </button>
                    <button wire:click="duplicar" class="btn btn-ghost btn-xs">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                        Duplicar
                    </button>
                    <button wire:click="eliminar" wire:confirm="¿Eliminar este registro?" class="btn btn-ghost btn-xs text-red-500 hover:bg-red-50">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table-adminlte">
                        <thead>
                            <tr>
                                @if($selectMode)
                                <th class="w-10">
                                    <input type="checkbox" wire:click="toggleSelectAll"
                                        {{ count($selectedIds) > 0 && !array_diff($registros->pluck('id')->toArray(), $selectedIds) ? 'checked' : '' }}>
                                </th>
                                @endif
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Chofer</th>
                                <th>Placa</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Clase</th>
                                <th>Hora Salida</th>
                                <th>Km. Salida</th>
                                <th>Hora Ingreso</th>
                                <th>Km. Ingreso</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $i => $r)
                                <tr wire:click="selectRegistro({{ $r['id'] }})"
                                    @if($selectMode) @click.stop @endif
                                    class="cursor-pointer {{ $selectMode && in_array($r['id'], $selectedIds) ? 'bg-ink-50 dark:bg-ink-800' : ($selectedId === $r['id'] && !$selectMode ? 'bg-ink-50 dark:bg-ink-800' : '') }}">
                                    @if($selectMode)
                                    <td class="w-10" wire:click.stop="toggleSelect({{ $r['id'] }})">
                                        <input type="checkbox" {{ in_array($r['id'], $selectedIds) ? 'checked' : '' }}>
                                    </td>
                                    @endif
                                    <td class="font-mono text-ink-400 text-xs">{{ $registros->firstItem() + $i }}</td>
                                    <td class="font-medium">{!! h($r['fecha'] ?? null, $search) !!}</td>
                                    <td class="font-medium text-ink-900 dark:text-ink-100 capitalize">{!! h($r['chofer'] ?? null, $search) !!}</td>
                                    <td class="font-mono font-medium text-ink-900 dark:text-ink-100">{!! h($r['placa'] ?? null, $search) !!}</td>
                                    <td>{!! h($r['marca'] ?? null, $search) !!}</td>
                                    <td>{!! h($r['modelo'] ?? null, $search) !!}</td>
                                    <td>{!! h($r['clase'] ?? null, $search) !!}</td>
                                    <td class="font-mono">{!! h($r['hora_salida'] ?? null) !!}</td>
                                    <td class="font-mono">{!! h($r['km_salida'] ?? null) !!}</td>
                                    <td class="font-mono">{!! h($r['hora_ingreso'] ?? null) !!}</td>
                                    <td class="font-mono">{!! h($r['km_ingreso'] ?? null) !!}</td>
                                    <td class="max-w-[200px] truncate">{!! h($r['observacion'] ?? null, $search) !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $selectMode ? 13 : 12 }}" class="px-3 py-16 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i data-lucide="truck" class="w-8 h-8 text-ink-300 dark:text-white/20"></i>
                                            </div>
                                            <p class="empty-state-title">No hay registros de control vehicular</p>
                                            <p class="empty-state-desc">Los registros aparecerán aquí una vez que se agreguen.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($registros->hasPages())
            <div class="card-footer">
                {{ $registros->links('livewire.olimpo.pagination-links') }}
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== TAB: Combustibles ==================== --}}
    <div x-show="tab === 'combustibles'">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-[32rem] sm:min-w-[32rem]">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400"></i>
                    <input type="text" wire:model.live="searchCombustible" placeholder="Buscar..."
                        class="input-field pl-9 h-9 w-full" />
                </div>

                <button
                    class="flex items-center gap-1.5 px-3 h-9 rounded-lg border border-[#e5eaef] dark:border-white/[0.06] bg-white dark:bg-ink-800/50 text-ink-400 dark:text-ink-500 hover:border-[#5D87FF] dark:hover:border-[#5D87FF] hover:text-[#5D87FF] dark:hover:text-[#5D87FF] transition-all duration-150 shrink-0"
                    :class="{ 'border-[#5D87FF] dark:border-[#5D87FF] text-[#5D87FF] dark:text-[#5D87FF] bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20': filtersOpenCombustible }"
                    @click="
                        if(filtersOpenCombustible) {
                            $wire.set('searchCombustible', '');
                            $wire.set('filterFechaCombustible', '');
                            $nextTick(() => filtersOpenCombustible = false);
                        } else {
                            filtersOpenCombustible = true;
                        }
                    "
                    :title="filtersOpenCombustible ? 'Cerrar y limpiar filtros' : 'Abrir filtros'">
                    <svg x-show="!filtersOpenCombustible" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 6V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                    <span x-show="!filtersOpenCombustible" class="text-xs font-medium">Filtrar</span>
                    <svg x-show="filtersOpenCombustible" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    <span x-show="filtersOpenCombustible" class="text-xs font-medium">Cerrar</span>
                </button>

                @php $afComb = ($searchCombustible ? 1 : 0) + ($filterFechaCombustible ? 1 : 0); @endphp
                <div x-show="!filtersOpenCombustible && {{ $afComb > 0 ? 'true' : 'false' }}" x-cloak>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#5D87FF]/10 dark:bg-[#5D87FF]/20 text-[#5D87FF] dark:text-[#5D87FF] border border-[#5D87FF]/20 dark:border-[#5D87FF]/40">
                        ● {{ $afComb }}
                    </span>
                </div>

                <div x-show="filtersOpenCombustible" class="flex items-center gap-3 flex-1 min-w-0 filter-slide-in">
                    <input type="date" wire:model.live="filterFechaCombustible" class="input-field h-9 w-56" title="Filtrar por fecha" />
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="nuevoCombustible" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                    Nuevo
                </button>
                <button wire:click="toggleSelectModeCombustible" class="btn btn-sm {{ $selectModeCombustible ? 'btn-warning' : 'btn-outline border-ink-200 dark:border-ink-600' }}">
                    {{ $selectModeCombustible ? 'Cancelar selección' : 'Seleccionar' }}
                </button>
                @if($selectModeCombustible)
                    <button wire:click="eliminarSeleccionadosCombustible" wire:confirm="¿Eliminar los registros de combustible seleccionados?" class="btn btn-danger btn-sm">
                        Eliminar ({{ count($selectedIdsCombustible) }})
                    </button>
                @endif
                <button wire:click="$dispatch('openImportModalFor', { panel: 'combustibles' })" class="btn btn-outline btn-sm border-ink-200 dark:border-ink-600 text-ink-600 dark:text-ink-400 hover:bg-ink-50 dark:hover:bg-white/[0.06]">
                    <i data-lucide="upload" class="w-4 h-4 mr-1"></i>
                    Importar
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="text-xs text-ink-500 dark:text-ink-400 font-medium">
                    {{ $searchCombustible ? "Resultados para '{$searchCombustible}'" : 'Registro de Combustibles' }}
                    <span class="text-ink-300">({{ $registrosCombustibles->total() }})</span>
                </span>
                @if($selectedCombustibleId && !$selectModeCombustible)
                <div class="flex items-center gap-1">
                    <button wire:click="editarCombustible" class="btn btn-ghost btn-xs">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        Editar
                    </button>
                    <button wire:click="duplicarCombustible" class="btn btn-ghost btn-xs">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                        Duplicar
                    </button>
                    <button wire:click="eliminarCombustible" wire:confirm="¿Eliminar este registro?" class="btn btn-ghost btn-xs text-red-500 hover:bg-red-50">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table-adminlte">
                        <thead>
                            <tr>
                                @if($selectModeCombustible)
                                <th class="w-10">
                                    <input type="checkbox" wire:click="toggleSelectAllCombustible"
                                        {{ count($selectedIdsCombustible) > 0 && !array_diff($registrosCombustibles->pluck('id')->toArray(), $selectedIdsCombustible) ? 'checked' : '' }}>
                                </th>
                                @endif
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cat.</th>
                                <th>Clase</th>
                                <th>Marca</th>
                                <th>Placa</th>
                                <th>Modelo</th>
                                <th>Año</th>
                                <th>Color</th>
                                <th>Conductor</th>
                                <th>Kilometraje</th>
                                <th>Combustible</th>
                                <th>Galones</th>
                                <th>Precio x Galón</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrosCombustibles as $i => $r)
                                <tr wire:click="selectCombustible({{ $r['id'] }})"
                                    @if($selectModeCombustible) @click.stop @endif
                                    class="cursor-pointer {{ $selectModeCombustible && in_array($r['id'], $selectedIdsCombustible) ? 'bg-ink-50 dark:bg-ink-800' : ($selectedCombustibleId === $r['id'] && !$selectModeCombustible ? 'bg-ink-50 dark:bg-ink-800' : '') }}">
                                    @if($selectModeCombustible)
                                    <td class="w-10" wire:click.stop="toggleSelectCombustible({{ $r['id'] }})">
                                        <input type="checkbox" {{ in_array($r['id'], $selectedIdsCombustible) ? 'checked' : '' }}>
                                    </td>
                                    @endif
                                    <td class="font-mono text-ink-400 text-xs">{{ $registrosCombustibles->firstItem() + $i }}</td>
                                    <td class="font-medium">{!! h($r['fecha'] ?? null, $searchCombustible) !!}</td>
                                    <td>{!! h($r['categoria'] ?? null, $searchCombustible) !!}</td>
                                    <td>{!! h($r['clase'] ?? null, $searchCombustible) !!}</td>
                                    <td>{!! h($r['marca'] ?? null, $searchCombustible) !!}</td>
                                    <td class="font-mono font-medium text-ink-900 dark:text-ink-100">{!! h($r['placa'] ?? null, $searchCombustible) !!}</td>
                                    <td>{!! h($r['modelo'] ?? null, $searchCombustible) !!}</td>
                                    <td>{!! h($r['anio'] ?? null) !!}</td>
                                    <td>{!! h($r['color'] ?? null) !!}</td>
                                    <td class="font-medium text-ink-900 dark:text-ink-100 capitalize">{!! h($r['conductor'] ?? null, $searchCombustible) !!}</td>
                                    <td class="font-mono">{!! h($r['kilometraje'] ?? null) !!}</td>
                                    <td class="font-medium">{!! h($r['combustible'] ?? null, $searchCombustible) !!}</td>
                                    <td class="text-right font-mono">{{ $r['galones'] ? number_format($r['galones'], 2) : '<span class="text-ink-300">—</span>' }}</td>
                                    <td class="text-right font-mono">{{ $r['precio_galon'] ? 'S/ ' . number_format($r['precio_galon'], 2) : '<span class="text-ink-300">—</span>' }}</td>
                                    <td class="text-right font-mono font-semibold text-ink-900 dark:text-ink-100">{{ $r['total'] ? 'S/ ' . number_format($r['total'], 2) : '<span class="text-ink-300">—</span>' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $selectModeCombustible ? 16 : 15 }}" class="px-3 py-16 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i data-lucide="fuel" class="w-8 h-8 text-ink-300 dark:text-white/20"></i>
                                            </div>
                                            <p class="empty-state-title">No hay registros de combustible</p>
                                            <p class="empty-state-desc">Los registros de combustible aparecerán aquí una vez que se agreguen.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($registrosCombustibles->hasPages())
            <div class="card-footer">
                {{ $registrosCombustibles->links('livewire.olimpo.pagination-links') }}
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== FORM MODAL: Control BMA-828 ==================== --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape.window="$wire.cancel"
        x-data
        x-transition:enter="transition-all duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        wire:key="form-modal">
        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg w-full max-w-xl max-h-[90vh] overflow-y-auto mx-4"
            x-transition:enter="transition-all duration-200 ease-out"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between px-6 py-4">
                <h4 class="font-semibold text-ink-900 dark:text-ink-100 text-sm font-display">{{ $editId ? 'Editar Registro' : 'Nuevo Registro' }}</h4>
                <button wire:click="cancel" class="p-1 text-ink-400 hover:text-ink-700 dark:hover:text-ink-300 rounded-lg hover:bg-ink-100 dark:hover:bg-white/[0.06]">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4">

                {{-- 1. Fecha / Hora Salida / Hora Ingreso --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Fecha</label>
                        <input type="text" wire:model="fecha" class="input-field w-full" placeholder="dd/mm/aaaa" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Hora Salida</label>
                        <input type="time" wire:model="hora_salida" class="input-field w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Hora Ingreso</label>
                        <input type="time" wire:model="hora_ingreso" class="input-field w-full" />
                    </div>
                </div>

                {{-- 2. Chofer / Placa --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div x-data="{ q: $wire.entangle('chofer').live || '', list: {{ Js::from($choferes) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Chofer</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Nombre del chofer..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ q: $wire.entangle('placa').live || '', list: {{ Js::from($placas) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Placa</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Placa..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Marca / Modelo / Clase --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div x-data="{ q: $wire.entangle('marca').live || '', list: {{ Js::from($marcas) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Marca</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Marca..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ q: $wire.entangle('modelo').live || '', list: {{ Js::from($modelos) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Modelo</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Modelo..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ q: $wire.entangle('clase').live || '', list: {{ Js::from($clases) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Clase</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Clase..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Km Salida / Km Ingreso --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Km. Salida</label>
                        <input type="text" wire:model="km_salida" class="input-field w-full" placeholder="Kilometraje de salida..." />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Km. Ingreso</label>
                        <input type="text" wire:model="km_ingreso" class="input-field w-full" placeholder="Kilometraje de ingreso..." />
                    </div>
                </div>

                {{-- 5. Observación --}}
                <div>
                    <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Observación</label>
                    <textarea wire:model="observacion" class="input-field w-full" rows="2" placeholder="Notas..."></textarea>
                </div>

            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-ink-100 dark:border-ink-700">
                <button wire:click="cancel" class="btn btn-secondary">Cancelar</button>
                <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="save" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span wire:loading.remove wire:target="save">{{ $editId ? 'Actualizar' : 'Guardar' }}</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== FORM MODAL: Combustibles ==================== --}}
    @if($showFormCombustible)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape.window="$wire.cancelCombustible"
        x-data
        x-transition:enter="transition-all duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        wire:key="form-modal-combustible">
        <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg w-full max-w-xl max-h-[90vh] overflow-y-auto mx-4"
            x-transition:enter="transition-all duration-200 ease-out"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between px-6 py-4">
                <h4 class="font-semibold text-ink-900 dark:text-ink-100 text-sm font-display">{{ $editIdCombustible ? 'Editar Combustible' : 'Nuevo Combustible' }}</h4>
                <button wire:click="cancelCombustible" class="p-1 text-ink-400 hover:text-ink-700 dark:hover:text-ink-300 rounded-lg hover:bg-ink-100 dark:hover:bg-white/[0.06]">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4">

                {{-- Fecha / Categoría --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Fecha</label>
                        <input type="text" wire:model="combFecha" class="input-field w-full" placeholder="dd/mm/aaaa" />
                        @error('combFecha') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div x-data="{ q: $wire.entangle('combCategoria').live || '', list: {{ Js::from($combCategorias) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Categoría</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Categoría..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clase / Marca --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div x-data="{ q: $wire.entangle('combClase').live || '', list: {{ Js::from($combClases) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Clase</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Clase..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ q: $wire.entangle('combMarca').live || '', list: {{ Js::from($combMarcas) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Marca</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Marca..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Placa / Modelo / Año / Color --}}
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div x-data="{ q: $wire.entangle('combPlaca').live || '', list: {{ Js::from($combPlacas) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Placa</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Placa..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ q: $wire.entangle('combModelo').live || '', list: {{ Js::from($combModelos) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Modelo</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Modelo..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Año</label>
                        <input type="text" wire:model="combAnio" class="input-field w-full" placeholder="Año..." />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Color</label>
                        <input type="text" wire:model="combColor" class="input-field w-full" placeholder="Color..." />
                    </div>
                </div>

                {{-- Conductor / Kilometraje --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div x-data="{ q: $wire.entangle('combConductor').live || '', list: {{ Js::from($combConductores) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Conductor</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Nombre del conductor..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Kilometraje</label>
                        <input type="text" wire:model="combKilometraje" class="input-field w-full" placeholder="Kilometraje..." />
                    </div>
                </div>

                {{-- Combustible / Galones --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div x-data="{ q: $wire.entangle('combCombustible').live || '', list: {{ Js::from($combustiblesList) }}, open: false }" class="relative">
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Combustible *</label>
                        <input type="text" x-model="q" @input="open = true" @focus="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            class="input-field w-full" placeholder="Tipo de combustible..." autocomplete="off" />
                        <div x-show="open && q.length > 0" x-cloak class="slide-enter absolute z-20 top-full mt-1 left-0 w-full max-h-48 overflow-y-auto">
                            <div class="dropdown-glass py-1">
                            <template x-for="v in list.filter(v => v.toLowerCase().includes(q.toLowerCase()))" :key="v">
                                <div @mousedown="q = v; open = false"
                                    class="px-3 py-2 text-sm text-ink-700 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-white/[0.06] cursor-pointer truncate"
                                    x-text="v"></div>
                            </template>
                            </div>
                        </div>
                        @error('combCombustible') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Galones *</label>
                        <input type="number" step="0.01" wire:model="combGalones" class="input-field w-full" placeholder="0.00" />
                        @error('combGalones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Precio x Galón / Total --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Precio x Galón</label>
                        <input type="number" step="0.01" wire:model="combPrecioGalon" class="input-field w-full" placeholder="0.00" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wider mb-1.5">Total</label>
                        <input type="number" step="0.01" wire:model="combTotal" class="input-field w-full" placeholder="0.00" />
                    </div>
                </div>

            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-ink-100 dark:border-ink-700">
                <button wire:click="cancelCombustible" class="btn btn-secondary">Cancelar</button>
                <button wire:click="saveCombustible" class="btn btn-primary" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="saveCombustible" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span wire:loading.remove wire:target="saveCombustible">{{ $editIdCombustible ? 'Actualizar' : 'Guardar' }}</span>
                    <span wire:loading wire:target="saveCombustible">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <livewire:olimpo.import-modal panel="control_vehiculos" wire:key="import-cv" />
</div>
