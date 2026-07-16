<div>
    <div class="card">
        <div class="card-header">
            <span class="font-semibold text-sm text-ink-900">Control de Camionetas</span>
            <span class="text-xs text-ink-400">{{ count($camionetas) }} camioneta(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table-adminlte">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Año</th>
                            <th>Color</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($camionetas as $i => $c)
                            <tr>
                                <td class="font-mono text-ink-400 text-xs">{{ $i + 1 }}</td>
                                <td class="font-mono font-medium text-ink-900">@if(!empty($c['placa'])){{ $c['placa'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td>@if(!empty($c['marca'])){{ $c['marca'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td>@if(!empty($c['modelo'])){{ $c['modelo'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td>@if(!empty($c['anio'])){{ $c['anio'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td>@if(!empty($c['color'])){{ $c['color'] }}@else<span class="text-ink-300">—</span>@endif</td>
                                <td>
                                    @if(!empty($c['estado']))
                                    <span class="badge {{ $c['estado'] === 'ACTIVO' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $c['estado'] }}
                                    </span>
                                    @else
                                    <span class="text-ink-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-12 text-center text-ink-400">No hay camionetas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
