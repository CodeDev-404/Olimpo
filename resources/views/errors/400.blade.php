<x-layouts.error code="400" icon="⚠️" title="Solicitud Incorrecta" description="El servidor no pudo entender la solicitud. Verifica los datos enviados e intenta de nuevo.">
    <a href="{{ route('olimpo.dashboard') }}" class="btn-primary">Volver al Inicio</a>
    <button class="btn-ghost" onclick="location.reload()">Reintentar</button>
</x-layouts.error>
