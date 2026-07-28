<x-layouts.error code="500" icon="💥" title="Error Interno" description="Ocurrió un error inesperado. El equipo de desarrollo ha sido notificado automáticamente.">
    <button class="btn-primary" onclick="location.reload()">Reintentar</button>
    <a href="{{ route('olimpo.dashboard') }}" class="btn-ghost">Volver al Inicio</a>
</x-layouts.error>
