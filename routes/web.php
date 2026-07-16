<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Olimpo\Dashboard;
use App\Livewire\Olimpo\Ocurrencias;
use App\Livewire\Olimpo\Asistencia;
use App\Livewire\Olimpo\Personal;
use App\Livewire\Olimpo\Recordatorios;
use App\Livewire\Olimpo\Cumpleanos;
use App\Livewire\Olimpo\RegistroPalm;
use App\Livewire\Olimpo\ControlCamionetas;
use App\Livewire\Olimpo\Reportes;
use App\Livewire\Olimpo\Graficos;
use App\Livewire\Olimpo\Configuracion;
use App\Livewire\Olimpo\ConsultasDni;
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
    Route::get('/olimpo/control-camionetas', ControlCamionetas::class)->name('olimpo.control-camionetas');
    Route::get('/olimpo/reportes', Reportes::class)->name('olimpo.reportes');
    Route::get('/olimpo/graficos', Graficos::class)->name('olimpo.graficos');
    Route::get('/olimpo/config', Configuracion::class)->name('olimpo.config');
    Route::get('/olimpo/consultas-dni', ConsultasDni::class)->name('olimpo.consultas-dni');
    Route::get('/olimpo/mas-herramientas', MasHerramientas::class)->name('olimpo.mas-herramientas');

    Route::get('/dashboard', function () {
        return redirect()->route('olimpo.dashboard');
    })->name('dashboard');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
