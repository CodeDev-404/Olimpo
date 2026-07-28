<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login">
        <div>
            <label for="email" class="form-label">{{ __('Correo electrónico') }}</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                class="input-field w-full" placeholder="admin@olimpo.com" />
            @error('form.email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4">
            <label for="password" class="form-label">{{ __('Contraseña') }}</label>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                class="input-field w-full" placeholder="••••••••" />
            @error('form.password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between mt-4">
            <label for="remember" class="inline-flex items-center gap-2 cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="w-4 h-4 rounded border-ink-300 dark:border-ink-600 text-[#5D87FF] focus:ring-[#5D87FF] dark:bg-ink-800 dark:focus:ring-[#5D87FF]" />
                <span class="text-sm text-ink-600 dark:text-ink-400">{{ __('Recordarme') }}</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-full justify-center mt-6">
            {{ __('Iniciar sesión') }}
        </button>

        @if (Route::has('password.request'))
            <div class="text-center mt-4">
                <a class="text-xs text-ink-400 hover:text-ink-600 dark:hover:text-ink-300 underline underline-offset-2" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            </div>
        @endif
    </form>
</div>
