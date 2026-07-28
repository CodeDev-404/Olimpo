<x-layouts.error code="429" icon="⏱️" title="Demasiadas Solicitudes" description="Has superado el límite de solicitudes. Espera unos momentos antes de intentar de nuevo.">
    <button class="btn-ghost" onclick="setTimeout(function() { location.reload(); }, 5000)">Reintentar en 5s</button>
    <a href="{{ route('olimpo.dashboard') }}" class="btn-primary">Volver al Inicio</a>
</x-layouts.error>
