<div>
    <div class="card mb-5">
        <div class="card-body">
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-[11px] text-ink-500 font-semibold uppercase tracking-wider">Año:</span>
                <select wire:model="anio" class="input-field w-28">
                    @foreach(range(now()->year, now()->year-4) as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
                <button wire:click="generar" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Generar Gráfico
                </button>
            </div>
        </div>
    </div>

    @if($hasData)
        <div class="grid grid-cols-1 gap-6">
            <div class="card">
                <div class="card-header">
                    <span class="font-semibold text-sm text-ink-900">{{ $chartData['title'] }}</span>
                </div>
                <div class="card-body">
                    <div style="height: 300px">
                        <canvas
                            x-data="{
                                init() {
                                    new Chart(this.$el, {
                                        type: 'bar',
                                        data: {
                                            labels: @js($chartData['labels']),
                                            datasets: [{
                                                label: 'Total',
                                                data: @js($chartData['data']),
                                                backgroundColor: '#1e293b',
                                                borderRadius: 4
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: {
                                                legend: { display: false }
                                            },
                                            scales: {
                                                y: {
                                                    ticks: { color: '#94a3b8', stepSize: 1 },
                                                    grid: { color: '#f1f5f9' }
                                                },
                                                x: {
                                                    ticks: { color: '#94a3b8' },
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

            @if($chartPieData)
                <div class="card">
                    <div class="card-header">
                        <span class="font-semibold text-sm text-ink-900">{{ $chartPieData['title'] }}</span>
                    </div>
                    <div class="card-body flex justify-center">
                        <div style="height: 300px; max-width: 400px; width: 100%">
                            <canvas
                                x-data="{
                                    init() {
                                        new Chart(this.$el, {
                                            type: 'pie',
                                            data: {
                                                labels: @js($chartPieData['labels']),
                                                datasets: [{
                                                    data: @js($chartPieData['data']),
                                                    backgroundColor: @js($chartPieData['colors'] ?? ['#1e293b', '#dc2626', '#f59e0b', '#16a34a', '#fd7e14', '#6f42c1', '#2563eb']),
                                                    borderWidth: 2,
                                                    borderColor: '#fff'
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: {
                                                        labels: { color: '#64748b' },
                                                        position: 'bottom'
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
            @endif
        </div>
    @else
        <div class="card">
            <div class="card-body py-16 text-center text-ink-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-ink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
                <p class="text-sm">Haz clic en "Generar Gráfico" para visualizar los datos</p>
            </div>
        </div>
    @endif
</div>
