<x-layouts.error code="419" icon="⏳" title="Sesión Expirada" description="Tu sesión ha expirado por inactividad. Recarga la página y vuelve a intentarlo.">
    <button class="btn-primary" onclick="location.reload()">Recargar Página</button>
    <a href="{{ route('login') }}" class="btn-ghost">Iniciar Sesión</a>
</x-layouts.error>
