<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;

class OtrosPendientes extends Component
{
    public function render()
    {
        return view('livewire.olimpo.otros-pendientes')
            ->layout('layouts.olimpo', ['title' => 'Otros Pendientes']);
    }
}
