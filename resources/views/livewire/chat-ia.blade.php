<div x-data="{ open: @entangle('open'), messages: @entangle('messages'), loading: @entangle('loading') }">
    <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-24 right-4 sm:right-6 z-[60] w-[calc(100vw-2rem)] max-w-sm flex flex-col h-[min(32rem,calc(100vh-8rem))] bg-white dark:bg-[#141e36] rounded-2xl shadow-2xl border border-ink-200 dark:border-white/[0.06] overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-[#5D87FF] to-[#49BEFF] shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-[#5D87FF]"></span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-white leading-tight truncate">OLIMPO AI</p>
                    <p class="text-[10px] text-white/80 leading-tight">Asistente del sistema</p>
                </div>
            </div>
            <button wire:click="toggle" class="w-7 h-7 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition-colors" title="Cerrar">
                <i data-lucide="x" class="w-4 h-4 text-white"></i>
            </button>
        </div>

        {{-- Messages --}}
        <div class="relative flex-1 overflow-y-auto p-3 space-y-3 bg-[#f6f8fa] dark:bg-[#0b1120]" id="chat-ia-messages"
             x-data="{ showTop: false, showBottom: false, checkScroll() { const el = this.$el; this.showTop = el.scrollTop > 60; this.showBottom = el.scrollHeight - el.scrollTop - el.clientHeight > 60; } }"
             @scroll.passive="checkScroll()"
             x-init="$watch('messages', () => $nextTick(() => { $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' }); checkScroll(); })); $watch('loading', () => $nextTick(() => { $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' }); checkScroll(); }))">
            <div class="absolute bottom-2.5 right-2.5 flex flex-col gap-1.5 z-10">
                <button x-show="showTop" x-cloak @click="document.getElementById('chat-ia-messages').scrollTo({ top: 0, behavior: 'smooth' })" class="w-7 h-7 rounded-full bg-white dark:bg-[#1C1F2E] border border-ink-200 dark:border-white/[0.08] shadow-md text-ink-500 dark:text-ink-300 hover:text-[#5D87FF] flex items-center justify-center transition-all" title="Ir al inicio de la conversación">
                    <i data-lucide="chevrons-up" class="w-4 h-4"></i>
                </button>
                <button x-show="showBottom" x-cloak @click="document.getElementById('chat-ia-messages').scrollTo({ top: document.getElementById('chat-ia-messages').scrollHeight, behavior: 'smooth' })" class="w-7 h-7 rounded-full bg-white dark:bg-[#1C1F2E] border border-ink-200 dark:border-white/[0.08] shadow-md text-ink-500 dark:text-ink-300 hover:text-[#5D87FF] flex items-center justify-center transition-all" title="Ir al final de la conversación">
                    <i data-lucide="chevrons-down" class="w-4 h-4"></i>
                </button>
            </div>
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="m.role === 'user'
                        ? 'bg-[#5D87FF] text-white rounded-2xl rounded-br-md px-3.5 py-2 max-w-[85%] text-sm whitespace-pre-line'
                        : 'bg-white dark:bg-[#141e36] text-ink-800 dark:text-white border border-ink-100 dark:border-white/[0.06] rounded-2xl rounded-bl-md px-3.5 py-2 max-w-[85%] text-sm whitespace-pre-line'">
                        <span x-html="window.chatLinkify(m.content || '')"></span>
                        <template x-if="m.razonamiento">
                            <span class="block mt-1.5 text-[10px] italic opacity-60 leading-snug border-t border-ink-100 dark:border-white/10 pt-1.5" x-text="'💭 ' + m.razonamiento"></span>
                        </template>
                    </div>
                </div>
            </template>
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white dark:bg-[#141e36] border border-ink-100 dark:border-white/[0.06] rounded-2xl rounded-bl-md px-3.5 py-2.5 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#5D87FF] animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#5D87FF] animate-bounce" style="animation-delay:120ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#5D87FF] animate-bounce" style="animation-delay:240ms"></span>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <form wire:submit="send" class="p-3 border-t border-ink-200 dark:border-white/[0.06] bg-white dark:bg-[#141e36] shrink-0">
            <div class="flex items-end gap-2">
                <textarea wire:model="message" rows="1" x-data="{}" x-init="$el.addEventListener('input', () => { $el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 100) + 'px'; }); $el.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.altKey && !e.shiftKey) { e.preventDefault(); if (!loading) $wire.send(); } else if (e.key === 'Enter' && (e.altKey || e.shiftKey)) { e.preventDefault(); const s = $el.selectionStart, en = $el.selectionEnd; $el.value = $el.value.slice(0, s) + '\n' + $el.value.slice(en); $el.selectionStart = $el.selectionEnd = s + 1; $el.dispatchEvent(new Event('input', { bubbles: true })); } })" placeholder="Pregúntame sobre el sistema..."
                    class="flex-1 resize-none bg-ink-50 dark:bg-white/[0.04] border border-ink-200 dark:border-white/[0.06] rounded-xl px-3 py-2 text-sm text-ink-900 dark:text-white placeholder:text-ink-400 dark:placeholder:text-ink-500 outline-none focus:border-[#5D87FF] focus:ring-2 focus:ring-[#5D87FF]/15 transition-all max-h-[100px]"></textarea>
                <button type="submit" :disabled="loading" class="w-9 h-9 shrink-0 rounded-xl bg-[#5D87FF] hover:bg-[#4B6FE0] text-white flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-[#5D87FF]/25">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- Floating button --}}
    <button x-on:click="$wire.toggle()" class="fixed bottom-6 right-4 sm:right-6 z-[60] w-14 h-14 rounded-full bg-gradient-to-br from-[#5D87FF] to-[#49BEFF] text-white flex items-center justify-center shadow-xl shadow-[#5D87FF]/40 hover:scale-105 active:scale-95 transition-transform" title="OLIMPO AI">
        <i data-lucide="sparkles" class="w-6 h-6" x-show="!open"></i>
        <i data-lucide="x" class="w-6 h-6" x-show="open" x-cloak></i>
    </button>
</div>

<script>
    window.chatLinkify = function (text) {
        var esc = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return esc.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener" class="text-[#5D87FF] underline break-all font-medium">$1</a>');
    };
</script>
