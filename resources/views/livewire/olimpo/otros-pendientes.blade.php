<div>
    <nav class="mb-4 flex items-center gap-1.5 text-xs text-ink-400 dark:text-ink-500">
        <a href="{{ route('olimpo.recordatorios') }}" wire:navigate class="hover:text-ink-600 dark:hover:text-ink-300 transition-colors">Recordatorios</a>
        <span class="text-ink-300">/</span>
        <span class="font-medium text-ink-600 dark:text-ink-300">Otros Pendientes</span>
    </nav>

    <div class="card">
        <div class="card-body py-16 text-center text-ink-400">
            <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-ink-200"></i>
            <p>No hay pendientes registrados.</p>
            <p class="text-sm mt-1">Aquí podrás gestionar otras tareas y recordatorios.</p>
        </div>
    </div>
</div>
