<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="font-size: 115%">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OLIMPO — {{ $title ?? 'Sistema de Control' }}</title>
    <script>if (localStorage.getItem('dark') === 'true') document.documentElement.classList.add('dark')</script>
    <link rel="icon" type="image/jpeg" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .table-adminlte td { text-transform: capitalize; }
    </style>
</head>
<body class="font-sans antialiased bg-ink-50 dark:bg-ink-950 text-ink-900 dark:text-ink-100 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        @persist('sidebar')
        <x-sidebar />
        @endpersist

        <div class="flex-1 flex flex-col overflow-hidden">
            @persist('header')
            <header class="bg-white dark:bg-[#1C1F2E] border-b border-[#e5eaef] dark:border-white/[0.06] h-20 flex items-center gap-4 px-5 lg:px-6 shrink-0 text-ink-600 dark:text-white/60">
                <div class="flex items-center gap-3">
        <button type="button"
            onclick="document.querySelector('.sidebar').classList.toggle('-translate-x-full'); var ov = document.querySelector('.sidebar-overlay'); if (ov) { if (document.querySelector('.sidebar').classList.contains('-translate-x-full')) { ov.classList.add('hidden'); } else { ov.classList.remove('hidden'); } }"
            class="lg:hidden p-2 text-ink-400 hover:text-ink-600 dark:hover:text-white/80 rounded-lg hover:bg-ink-100 dark:hover:bg-white/[0.06]">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
                </div>
                <div class="flex-1">
                    {{-- Global Search --}}
                    <div class="relative hidden sm:block" x-data="{
                        q: '',
                        focused: false,
                        scope: 'ocurrencias',
                        results: [],
                        showWindow: false,
                        timer: null,
                        get scopeLabel() {
                            return this.scope === 'ocurrencias' ? 'Ocurrencias' : this.scope === 'control-vehiculos' ? 'Control Vehículos' : 'Todos';
                        },
                        search(keepOpen = false) {
                            if (this.q.trim().length < 1) { this.results = []; if (!keepOpen) this.showWindow = false; return; }
                            this.showWindow = true;
                            fetch('{{ route('olimpo.search') }}?q=' + encodeURIComponent(this.q.trim()) + '&scope=' + this.scope)
                                .then(r => r.json())
                                .then(data => { this.results = data; })
                                .catch(() => { this.results = []; });
                        },
                        init() {
                            this.$watch('q', () => {
                                clearTimeout(this.timer);
                                this.timer = setTimeout(() => this.search(), 300);
                            });
                            this.$watch('scope', () => {
                                if (this.showWindow && this.q.trim().length > 0) this.search(true);
                            });
                        }
                    }">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center bg-[#f4f6f9] dark:bg-white/[0.06] rounded-lg overflow-hidden text-sm"
                                :class="{ 'ring-2 ring-[#5D87FF]/50': focused || q.length > 0 }">
                                <span class="flex items-center justify-center px-3 self-stretch transition-colors duration-150"
                                    :class="focused || q.length > 0 ? 'bg-[#5D87FF] text-white' : 'text-ink-400'">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                </span>
                                <input type="text" x-model="q" placeholder="Buscar en el sistema..."
                                    class="bg-transparent border-0 outline-none focus:outline-none focus:ring-0 py-2.5 pl-2 min-w-[24rem] text-sm text-ink-700 dark:text-ink-300 placeholder-ink-400"
                                    @focus="focused = true"
                                    @blur="focused = false"
                                    @keydown.escape.window="showWindow = false"
                                    @keydown.enter="q.trim() ? (showWindow = true, search(true)) : null">

                            </div>
                        </div>

                        {{-- Floating results window (teleported to body to escape header stacking context) --}}
                        <template x-teleport="body">
                            <div x-show="showWindow" x-cloak
                                class="fixed inset-0 z-50 flex items-start justify-center pt-16"
                                @click.self="showWindow = false">
                            <div class="bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg border border-[#e5eaef] dark:border-white/[0.06] w-full max-w-6xl max-h-[80vh] flex flex-col overflow-hidden mx-4"
                                @click.stop>
                                {{-- Header --}}
                                <div                                      class="flex items-center justify-between px-6 py-4 border-b border-[#e5eaef] dark:border-white/[0.06] shrink-0">
                                    <h3 class="text-sm font-bold text-ink-800 dark:text-white/80">
                                        Resultados de: <span class="text-[#5D87FF]" x-text="scopeLabel"></span>
                                    </h3>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-ink-500 dark:text-ink-400 font-medium">Buscar:</span>
                                        <select x-model="scope"
                                            class="bg-[#f4f6f9] dark:bg-white/[0.06] border-0 text-xs text-ink-500 dark:text-white/60 py-1.5 px-6 rounded-lg outline-none cursor-pointer font-medium">
                                            <option value="ocurrencias">Ocurrencias</option>
                                            <option value="control-vehiculos">Vehículos</option>
                                            <option value="todos">Todos</option>
                                        </select>
                                        <button @click="showWindow = false" class="p-1 text-ink-400 hover:text-ink-600 dark:hover:text-ink-300 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-800">
                                            <i data-lucide="x" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Table --}}
                                <div class="overflow-auto flex-1">
                                    <template x-if="results.length > 0">
                                        <table class="w-full text-sm whitespace-nowrap">
                                            {{-- Ocurrencias full table --}}
                                            <template x-if="scope === 'ocurrencias'">
                                                <thead class="sticky top-0 bg-ink-50 dark:bg-ink-900">
                                                    <tr>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider w-8">#</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Fecha</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Ingreso</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Salida</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Nombre</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Vehículo</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Destino</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Motivo</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Detalles</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Obs.</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Cargo</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Tipo</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Otro</th>
                                                        <th class="text-left px-2 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Turno</th>
                                                        <th class="px-2 py-2.5 text-right">
                                                            <a :href="'{{ route('olimpo.ocurrencias') }}?search=' + encodeURIComponent(q)" class="text-[11px] font-semibold text-[#5D87FF] uppercase tracking-wider">Ver todo →</a>
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </template>
                                            <template x-if="scope === 'ocurrencias'">
                                                <tbody>
                                                    <template x-for="r in results" :key="r._idx">
                                                        <tr class="border-t border-ink-100 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800/50 transition-colors">
                                                            <td class="px-2 py-2 text-xs text-ink-400 tabular-nums w-8" x-text="r._idx"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 whitespace-nowrap" x-text="r.fecha"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 tabular-nums whitespace-nowrap" x-text="r.hora_ingreso || '—'"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 tabular-nums whitespace-nowrap" x-text="r.hora_salida || '—'"></td>
                                                            <td class="px-2 py-2 text-xs font-medium text-ink-900 dark:text-ink-100 max-w-[140px] truncate" x-text="r.persona" :title="r.persona"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 max-w-[90px] truncate" x-text="r.vehiculo || '—'" :title="r.vehiculo"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 max-w-[100px] truncate" x-text="r.destino || '—'" :title="r.destino"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 max-w-[100px] truncate" x-text="r.motivo || '—'" :title="r.motivo"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 max-w-[120px] truncate" x-text="r.detalles || '—'" :title="r.detalles"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 max-w-[100px] truncate" x-text="r.observacion || '—'" :title="r.observacion"></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 max-w-[100px] truncate" x-text="r.cargo || '—'" :title="r.cargo"></td>
                                                            <td class="px-2 py-2"><span class="text-[10px] font-semibold uppercase px-1 py-0.5 rounded-lg bg-ink-100 dark:bg-ink-800 text-ink-600 dark:text-ink-400" x-text="r.tipo || '—'"></span></td>
                                                            <td class="px-2 py-2 text-xs text-ink-600 dark:text-ink-400 max-w-[80px] truncate" x-text="r.otro || '—'" :title="r.otro"></td>
                                                            <td class="px-2 py-2"><span class="text-[10px] font-semibold uppercase px-1 py-0.5 rounded-lg" :class="r.turno === 'NOCHE' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-ember-100 text-ember-700 dark:bg-ember-900/30 dark:text-ember-300'" x-text="r.turno || 'DÍA'"></span></td>
                                                            <td class="px-2 py-2">
                                                                <a :href="r._url" class="text-xs text-[#5D87FF] font-medium whitespace-nowrap">Abrir →</a>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </template>

                                            {{-- Control Vehículos full table --}}
                                            <template x-if="scope === 'control-vehiculos'">
                                                <thead class="sticky top-0 bg-ink-50 dark:bg-ink-900">
                                                    <tr>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Placa</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Chofer</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Fecha</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Marca</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Modelo</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">H.Salida</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Km.Salida</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">H.Ingreso</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Km.Ingreso</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Obs.</th>
                                                        <th class="px-3 py-2.5 text-right">
                                                            <a :href="'{{ route('olimpo.control-vehiculos') }}?search=' + encodeURIComponent(q)" class="text-[11px] font-semibold text-[#5D87FF] uppercase tracking-wider">Ver todo →</a>
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </template>
                                            <template x-if="scope === 'control-vehiculos'">
                                                <tbody>
                                                    <template x-for="r in results" :key="r._scope + r.fecha + r.placa">
                                                        <tr class="border-t border-ink-100 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800/50 transition-colors">
                                                            <td class="px-3 py-2 font-medium text-ink-900 dark:text-ink-100" x-text="r.placa"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400 max-w-[140px] truncate" x-text="r.chofer" :title="r.chofer"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400" x-text="r.fecha"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400" x-text="r.marca || '—'"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400" x-text="r.modelo || '—'"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400 tabular-nums" x-text="r.hora_salida || '—'"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400 tabular-nums" x-text="r.km_salida || '—'"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400 tabular-nums" x-text="r.hora_ingreso || '—'"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400 tabular-nums" x-text="r.km_ingreso || '—'"></td>
                                                            <td class="px-3 py-2 text-ink-600 dark:text-ink-400 max-w-[100px] truncate" x-text="r.observacion || '—'" :title="r.observacion"></td>
                                                            <td class="px-3 py-2">
                                                                <a :href="r._url" class="text-xs text-[#5D87FF] font-medium">Abrir →</a>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </template>

                                            {{-- Todos: unified view --}}
                                            <template x-if="scope === 'todos'">
                                                <thead class="sticky top-0 bg-ink-50 dark:bg-ink-900">
                                                    <tr>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Registro</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Detalle</th>
                                                        <th class="text-left px-3 py-2.5 text-[11px] font-semibold text-ink-500 uppercase tracking-wider">Tipo</th>
                                                        <th class="w-16"></th>
                                                    </tr>
                                                </thead>
                                            </template>
                                            <template x-if="scope === 'todos'">
                                                <tbody>
                                                    <template x-for="r in results" :key="r._scope + (r.fecha || '') + (r.persona || r.placa)">
                                                        <tr class="border-t border-ink-100 dark:border-ink-700 hover:bg-ink-50 dark:hover:bg-ink-800/50 transition-colors">
                                                            <td class="px-3 py-2 font-medium text-ink-900 dark:text-ink-100 max-w-[180px] truncate" x-text="r.persona || r.chofer || r.placa" :title="r.persona || r.chofer || r.placa"></td>
                                                            <td class="px-3 py-2 text-ink-500 dark:text-ink-400 max-w-[300px] truncate" x-text="r.fecha + ' · ' + (r.vehiculo || r.placa || '—')" :title="r.fecha + ' · ' + (r.vehiculo || r.placa || '—')"></td>
                                                            <td class="px-3 py-2">
                                                                <span class="inline-block text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded-lg bg-ink-100 dark:bg-ink-800 text-ink-500 dark:text-ink-400" x-text="r._scope === 'ocurrencias' ? 'Ocurrencia' : 'Vehículo'"></span>
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <a :href="r._url" class="text-xs text-[#5D87FF] font-medium">Abrir →</a>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </template>
                                        </table>
                                    </template>
                                    <template x-if="results.length === 0">
                                        <div class="px-6 py-8 text-center text-sm text-ink-400">
                                            Sin resultados para "<span class="font-medium text-ink-600 dark:text-white/60" x-text="q"></span>"
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        </template>
                    </div>
                </div>
                <div class="flex items-center gap-4 ml-auto">
                    {{-- Weather --}}
                    <div class="flex items-center gap-1" x-data="{
                        weather: null,
                        weatherError: false,
                        loading: false,
                        lat: null,
                        lon: null,
                        init() {
                            var cached = null;
                            try { cached = JSON.parse(localStorage.getItem('olimpo_weather')); } catch(e) {}
                            if (cached && (Date.now() - cached.ts < 600000)) {
                                this.weather = cached.data;
                            } else {
                                this.fetchWeather(true);
                            }
                            setInterval(function() { this.fetchWeather(false); }.bind(this), 600000);
                        },
                        fetchWeather(requestGeolocation) {
                            var self = this;
                            self.loading = true;
                            self.weatherError = false;
                            var doFetch = function(lat, lon) {
                                var url = lat != null && lon != null
                                    ? 'https://wttr.in/' + lat + ',' + lon + '?format=j1'
                                    : 'https://wttr.in?format=j1';
                                var controller = new AbortController();
                                var timer = setTimeout(function() { controller.abort(); }, 5000);
                                fetch(url, { signal: controller.signal })
                                    .then(function(r) { clearTimeout(timer); return r.json(); })
                                    .then(function(data) {
                                        var c = data.current_condition[0];
                                        var wData = {
                                            temp: c.temp_C,
                                            code: c.weatherCode,
                                            city: data.nearest_area[0].areaName[0].value,
                                        };
                                        self.weather = wData;
                                        self.loading = false;
                                        try { localStorage.setItem('olimpo_weather', JSON.stringify({ data: wData, ts: Date.now() })); } catch(e) {}
                                    })
                                    .catch(function() { clearTimeout(timer); self.weatherError = true; self.loading = false; });
                            };
                            if (requestGeolocation && navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    function(pos) {
                                        self.lat = pos.coords.latitude;
                                        self.lon = pos.coords.longitude;
                                        doFetch(self.lat, self.lon);
                                    },
                                    function() { doFetch(self.lat, self.lon); },
                                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                                );
                            } else {
                                doFetch(self.lat, self.lon);
                            }
                        },
                        get icon() {
                            if (!this.weather) return '';
                            var code = parseInt(this.weather.code);
                            var temp = parseInt(this.weather.temp);
                            if (code >= 200 && code < 300) return '⛈';
                            if (code >= 300 && code < 400) return temp > 20 ? '🌦' : '🌧';
                            if (code >= 500 && code < 600) return '🌧';
                            if (code >= 600 && code < 700) return '❄️';
                            if (code >= 700 && code < 800) return '🌫';
                            if (code === 800) return temp > 28 ? '🔥' : temp > 20 ? '☀️' : '🌤';
                            if (code > 800) return temp > 25 ? '⛅' : temp > 15 ? '⛅' : '☁️';
                            return temp > 25 ? '☀️' : temp > 15 ? '🌤' : '☁️';
                        },
                        get desc() {
                            if (!this.weather) return '';
                            var code = parseInt(this.weather.code);
                            var temp = parseInt(this.weather.temp);
                            if (code >= 200 && code < 300) return 'Tormenta';
                            if (code >= 300 && code < 400) return temp > 20 ? 'Lluvia ligera' : 'Lluvia';
                            if (code >= 500 && code < 600) return 'Lluvia';
                            if (code >= 600 && code < 700) return 'Nieve';
                            if (code >= 700 && code < 800) return 'Niebla';
                            if (code === 800) return temp > 28 ? 'Caluroso' : temp > 20 ? 'Soleado' : 'Despejado';
                            if (code > 800) return temp > 25 ? 'Parcialmente soleado' : temp > 15 ? 'Parcialmente nublado' : 'Nublado';
                            return temp > 25 ? 'Soleado' : temp > 15 ? 'Parcialmente nublado' : 'Nublado';
                        },
                        get weatherEmoji() {
                            if (!this.weather) return '';
                            var code = parseInt(this.weather.code);
                            var temp = parseInt(this.weather.temp);
                            if (code >= 200 && code < 300) return '⛈';
                            if (code >= 300 && code < 400) return temp > 20 ? '🌦' : '🌧';
                            if (code >= 500 && code < 600) return '🌧';
                            if (code >= 600 && code < 700) return '❄️';
                            if (code >= 700 && code < 800) return '🌫';
                            if (code === 800) return temp > 28 ? '🔥' : temp > 20 ? '☀️' : '🌤';
                            if (code > 800) return temp > 25 ? '⛅' : temp > 15 ? '⛅' : '☁️';
                            return temp > 25 ? '☀️' : temp > 15 ? '🌤' : '☁️';
                        },
                    }">
                        <template x-if="weather">
                            <div style="display: grid; grid-template-columns: max-content; grid-template-areas: 'location location' 'icon temp' 'desc desc'; gap: 0 4px; align-items: center;">
                                <div style="grid-area: location;" class="text-right text-[11px] text-ink-500 dark:text-ink-400 leading-tight" x-text="weather.city"></div>
                                <div style="grid-area: icon; display: flex; justify-content: end; align-items: center;">
                                    <span class="text-xl" x-text="weatherEmoji"></span>
                                </div>
                                <div style="grid-area: temp;" class="text-right">
                                    <div style="line-height: 1.5rem; font-size: 1.3rem;" class="font-bold text-ink-800 dark:text-ink-100" x-text="weather.temp + '°'"></div>
                                </div>
                                <div style="grid-area: desc;" class="text-right text-xs text-ink-400 dark:text-ink-500" x-text="desc"></div>
                            </div>
                        </template>
                        <template x-if="!weather && !weatherError">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-ink-100 dark:bg-ink-800 animate-pulse"></div>
                                <div class="space-y-1">
                                    <div class="w-10 h-3.5 rounded-lg bg-ink-100 dark:bg-ink-800 animate-pulse"></div>
                                    <div class="w-8 h-2.5 rounded-lg bg-ink-100 dark:bg-ink-800 animate-pulse"></div>
                                </div>
                            </div>
                        </template>
                        <button x-show="weather && !loading" @click="fetchWeather(true)" class="p-1.5 rounded-lg hover:bg-ink-100 dark:hover:bg-ink-700 transition-colors text-ink-400 dark:text-ink-500 hover:text-ink-600 dark:hover:text-ink-300" title="Actualizar ubicación">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        </button>
                        <span x-show="loading" class="text-xs text-ink-400 dark:text-ink-500 animate-spin inline-block"><i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i></span>
                    </div>
                    <span class="w-px h-8 bg-ink-200 dark:bg-ink-700"></span>
                    {{-- Clock --}}
                    <div x-data="{
                        now: new Date(),
                        init() { setInterval(() => { this.now = new Date(); }, 1000); },
                        get date() {
                            return this.now.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' })
                                .replace(/^(\w)/, (c) => c.toUpperCase())
                                .replace(/de (\w)/, (_, m) => 'de ' + m.toUpperCase());
                        },
                        get time() { return this.now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }); }
                    }" class="select-none">
                        <div class="text-xs font-medium text-ink-400 dark:text-ink-500 leading-tight" x-text="date"></div>
                        <div class="text-right text-3xl font-bold text-ink-800 dark:text-ink-100 leading-none tracking-tight tabular-nums" x-text="time"></div>
                    </div>
                    <span class="w-px h-8 bg-ink-200 dark:bg-ink-700"></span>
                    @php $user = auth()->user(); $hasPhoto = $user->profile_photo_path && Storage::exists($user->profile_photo_path); @endphp
                        <button onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('dark', document.documentElement.classList.contains('dark'))" class="p-1.5 text-ink-400 hover:text-ink-600 dark:hover:text-white/80 rounded-lg hover:bg-ink-100 dark:hover:bg-white/[0.06] transition-all duration-200" title="Alternar modo oscuro">
                        <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
                        <i data-lucide="sun" class="w-4 h-4 hidden dark:inline-block"></i>
                    </button>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-ink-100 dark:hover:bg-white/[0.06] transition-all duration-150 cursor-pointer">
                            <div class="w-8 h-8 rounded-lg shrink-0 overflow-hidden @if(!$hasPhoto) bg-[#5D87FF]/10 flex items-center justify-center @endif">
                                @if($hasPhoto)
                                    <img src="{{ Storage::url($user->profile_photo_path) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs font-bold text-[#5D87FF]">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="text-left hidden sm:block">
                            <p class="text-sm text-ink-900 dark:text-white font-medium leading-tight">{{ $user->name }}</p>
                            <p class="text-[10px] text-ink-400 dark:text-white/50 capitalize font-medium leading-tight">{{ $user->role }}</p>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-ink-400 shrink-0" x-bind:class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-cloak
                            class="absolute right-0 top-full mt-2 w-64 bg-white dark:bg-[#1C1F2E] rounded-xl shadow-lg border border-[#e5eaef] dark:border-white/[0.06] overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-[#e5eaef] dark:border-white/[0.06]">
                                <p class="font-semibold text-ink-900 dark:text-white text-sm truncate">{{ $user->name }}</p>
                                <p class="text-xs text-ink-500 dark:text-white/50 truncate">{{ $user->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('olimpo.mi-cuenta') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06] transition-colors">
                                    <i data-lucide="user" class="w-4 h-4 text-ink-400"></i>Mi perfil
                                </a>
                                <a href="{{ route('olimpo.config') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-ink-700 dark:text-white/70 hover:bg-ink-50 dark:hover:bg-white/[0.06] transition-colors">
                                    <i data-lucide="settings" class="w-4 h-4 text-ink-400"></i>Configuración
                                </a>
                            </div>
                            <div class="border-t border-[#e5eaef] dark:border-white/[0.06] py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>Salir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            @endpersist

            @php
                $routeName = request()->route()?->getName();
                $allPanels = config('olimpo.panels', []);
                $info = $allPanels[$routeName] ?? ['icon' => 'circle', 'desc' => ''];
            @endphp
            <main class="flex-1 overflow-y-auto p-4 lg:p-6 bg-[#f6f8fa] dark:bg-[#0c0c14]">
                <div class="mb-6 flex items-center gap-4">
                    <div class="page-header-icon">
                        <i data-lucide="{{ $info['icon'] }}" class="w-5 h-5 text-[#5D87FF]"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-ink-900 dark:text-ink-100 select-none tracking-tight">{{ $title ?? '' }}</h1>
                        @if($info['desc'])
                        <p class="text-sm text-ink-500 dark:text-ink-400 mt-0.5">{{ $info['desc'] }}</p>
                        @endif
                    </div>
                </div>
                {{ $slot }}
            </main>
        </div>
    </div>

    <div
        x-data="{ show: false, message: '', type: 'success', timer: null }"
        x-on:notify.window="
            let msg = $event.detail.message || $event.detail[0]?.message || '';
            let typ = $event.detail.type || $event.detail[0]?.type || 'success';
            if (msg) {
                clearTimeout(this.timer);
                show = true; message = msg; type = typ;
                this.timer = setTimeout(() => show = false, 5000);
            }
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-[9999] px-4 py-2.5 rounded-lg shadow-lg text-sm font-semibold text-white max-w-sm"
        :class="type === 'success' ? 'bg-[#13DEB9]' : (type === 'warning' ? 'bg-[#FFAE1F]' : (type === 'danger' || type === 'error' ? 'bg-[#FA896B]' : 'bg-[#539BFF]'))"
        x-text="message"
    ></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
