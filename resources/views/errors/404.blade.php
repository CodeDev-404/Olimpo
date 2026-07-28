<x-layouts.error code="404" icon="🔍" title="Página No Encontrada" description="La página que buscas no existe o fue movida. Revisa la URL o vuelve al inicio.">
    <a href="{{ route('olimpo.dashboard') }}" class="btn-primary">Volver al Inicio</a>
    <a href="{{ url()->previous() }}" class="btn-ghost">Página Anterior</a>
</x-layouts.error>
