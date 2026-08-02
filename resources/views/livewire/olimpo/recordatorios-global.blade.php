<div x-data="recordatoriosGlobal(@entangle('due'))" x-init="init()" x-cloak></div>

@push('scripts')
<script>
function recordatoriosGlobal(due) {
    return {
        due: due || [],
        init() {
            this.requestPermission();
            this.fire();
            this.$watch('due', () => this.fire());
            setInterval(() => this.$wire.refresh(), 60000);
            document.addEventListener('click', () => this.requestPermission(), { once: true });
        },
        requestPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },
        fire() {
            let now = new Date();
            let hhmm = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
            (this.due || []).forEach(d => {
                let hora = (d.hora || '07:30').substring(0, 5);
                let key = (d.tipo === 'prog' ? 'prog_notified_' : 'cumple_notified_') + d.id + '_' + now.toDateString();
                if (hhmm >= hora && !sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    let title = d.tipo === 'prog' ? '⏰ ¡Recordatorio programado!' : '🎂 ¡Recordatorio de Cumpleaños!';
                    let body = (d.tipo === 'prog' ? 'Recuerda: ' : 'Hoy cumple: ') + d.nombre + (d.parentesco ? ' (' + d.parentesco + ')' : '');
                    if ('Notification' in window && Notification.permission === 'granted') {
                        new Notification(title, { body: body, icon: '/favicon.ico' });
                    }
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: body, type: 'success' } }));
                }
            });
        }
    };
}
</script>
@endpush
