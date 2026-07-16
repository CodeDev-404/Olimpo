<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $hoy = now()->format('d/m');
    $cumpleanos = \App\Models\Cumpleano::where('fecha', $hoy)
        ->where('recordatorio_activo', true)
        ->get();

    foreach ($cumpleanos as $cumpleano) {
        $hora = $cumpleano->recordatorio_hora ?? '07:30';
        // Aquí se enviaría la notificación (email, push, etc.)
        // Por ahora solo log
        \Log::info("Recordatorio cumpleaños: {$cumpleano->nombre} ({$cumpleano->parentesco}) a las {$hora}");
    }
})->dailyAt('07:30')->name('cumpleanos-recordatorio');
