<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Olimpo\Dashboard;
use App\Livewire\Olimpo\Ocurrencias;
use App\Livewire\Olimpo\Asistencia;
use App\Livewire\Olimpo\Personal;
use App\Livewire\Olimpo\Recordatorios;
use App\Livewire\Olimpo\Cumpleanos;
use App\Livewire\Olimpo\RegistroPalm;
use App\Livewire\Olimpo\ControlVehiculos;
use App\Livewire\Olimpo\Configuracion;
use App\Livewire\Olimpo\MasHerramientas;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/olimpo/dashboard', Dashboard::class)->name('olimpo.dashboard');
    Route::get('/olimpo/ocurrencias', Ocurrencias::class)->name('olimpo.ocurrencias');
    Route::get('/olimpo/asistencia', Asistencia::class)->name('olimpo.asistencia');
    Route::get('/olimpo/personal', Personal::class)->name('olimpo.personal');
    Route::get('/olimpo/recordatorios', Recordatorios::class)->name('olimpo.recordatorios');
    Route::get('/olimpo/cumpleanos', Cumpleanos::class)->name('olimpo.cumpleanos');
    Route::get('/olimpo/registro-palm', RegistroPalm::class)->name('olimpo.registro-palm');
    Route::get('/olimpo/control-vehiculos', ControlVehiculos::class)->name('olimpo.control-vehiculos');
    Route::get('/olimpo/configuracion', Configuracion::class)->name('olimpo.config');
    Route::get('/olimpo/mas-herramientas', MasHerramientas::class)->name('olimpo.mas-herramientas');
    Route::view('/olimpo/mi-cuenta', 'profile')->name('olimpo.mi-cuenta');

    Route::get('/olimpo/search', function (\Illuminate\Http\Request $request) {
        $q = $request->input('q', '');
        $scope = $request->input('scope', 'ocurrencias');
        if (strlen($q) < 1) return response()->json([]);

        $results = [];

        if ($scope === 'ocurrencias' || $scope === 'todos') {
            $ocurrencias = \App\Models\Ocurrencia::with('persona')
                ->where('persona_nombre', 'like', "%{$q}%")
                ->orWhere('vehiculo', 'like', "%{$q}%")
                ->orWhere('destino', 'like', "%{$q}%")
                ->orWhere('motivo', 'like', "%{$q}%")
                ->orWhere('detalles', 'like', "%{$q}%")
                ->orWhere('observacion', 'like', "%{$q}%")
                ->orWhere('otro', 'like', "%{$q}%")
                ->orderBy('fecha', 'desc')
                ->orderBy('id', 'desc')
                ->limit(30)
                ->get()
                ->map(fn($o, $i) => [
                    '_scope' => 'ocurrencias',
                    '_url' => route('olimpo.ocurrencias') . '?search=' . urlencode($o->persona_nombre),
                    '_idx' => $i + 1,
                    'persona' => $o->persona_nombre,
                    'fecha' => $o->fecha,
                    'hora_ingreso' => $o->hora_ingreso,
                    'hora_salida' => $o->hora_salida,
                    'vehiculo' => $o->vehiculo,
                    'destino' => $o->destino,
                    'motivo' => $o->motivo,
                    'detalles' => $o->detalles,
                    'observacion' => $o->observacion,
                    'cargo' => $o->persona?->cargo?->nombre ?? $o->persona?->cargo ?? '',
                    'tipo' => $o->tipo,
                    'otro' => $o->otro,
                    'turno' => $o->turno,
                ]);
            $results = array_merge($results, $ocurrencias->toArray());
        }

        if ($scope === 'control-vehiculos' || $scope === 'todos') {
            $vehiculos = \App\Models\ControlVehiculo::where('chofer', 'like', "%{$q}%")
                ->orWhere('placa', 'like', "%{$q}%")
                ->orWhere('marca', 'like', "%{$q}%")
                ->orWhere('modelo', 'like', "%{$q}%")
                ->orWhere('observacion', 'like', "%{$q}%")
                ->orderBy('fecha', 'desc')
                ->limit(20)
                ->get()
                ->map(fn($v) => [
                    '_scope' => 'control-vehiculos',
                    '_url' => route('olimpo.control-vehiculos') . '?search=' . urlencode($v->chofer),
                    'placa' => $v->placa,
                    'chofer' => $v->chofer,
                    'fecha' => $v->fecha,
                    'marca' => $v->marca,
                    'modelo' => $v->modelo,
                    'hora_salida' => $v->hora_salida,
                    'km_salida' => $v->km_salida,
                    'hora_ingreso' => $v->hora_ingreso,
                    'km_ingreso' => $v->km_ingreso,
                    'observacion' => $v->observacion,
                ]);
            $results = array_merge($results, $vehiculos->toArray());
        }

        return response()->json($results);
    })->name('olimpo.search');

    Route::get('/dashboard', function () {
        return redirect()->route('olimpo.dashboard');
    })->name('dashboard');


    Route::get('/olimpo/download-video/{filename}', function ($filename) {
        $path = storage_path('app/temp/downloads/' . basename($filename));
        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado');
        }
        return response()->download($path);
    })->name('olimpo.download-video');

    Route::get('/olimpo/download-convert/{filename}', function ($filename) {
        $path = storage_path('app/temp/convert/' . basename($filename));
        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado');
        }
        return response()->download($path);
    })->name('olimpo.download-convert');

    Route::post('/olimpo/upload-base64', function (\Illuminate\Http\Request $request) {
        $name = $request->header('X-File-Name', 'file');
        $ext = pathinfo(urldecode($name), PATHINFO_EXTENSION) ?: 'bin';
        $filename = uniqid('conv_') . '.' . $ext;
        $path = 'temp/convert_uploads/' . $filename;
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $data = $request->input('data');
        if ($data === null || $data === '') {
            return response()->json(['error' => 'No data received'], 400);
        }
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return response()->json(['error' => 'Invalid base64'], 400);
        }
        file_put_contents($fullPath, $decoded);
        $previewUrl = $ext ? route('olimpo.preview-upload', ['filename' => $filename], false) : '';
        return response()->json(['path' => $path, 'name' => urldecode($name), 'preview_url' => $previewUrl]);
    })->name('olimpo.upload-base64');

    Route::get('/olimpo/preview-upload/{filename}', function ($filename) {
        $path = \Illuminate\Support\Facades\Storage::disk('local')->path('temp/convert_uploads/' . basename($filename));
        if (!file_exists($path)) {
            abort(404);
        }
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        return response()->file($path, ['Content-Type' => $mime]);
    })->name('olimpo.preview-upload');

    Route::get('/olimpo/preview-video/{filename}', function ($filename) {
        $path = \Illuminate\Support\Facades\Storage::disk('local')->path('temp/videos/' . basename($filename));
        if (!file_exists($path)) {
            abort(404);
        }
        $mime = mime_content_type($path) ?: 'video/mp4';
        return response()->file($path, ['Content-Type' => $mime]);
    })->name('olimpo.preview-video');
});

require __DIR__.'/auth.php';
