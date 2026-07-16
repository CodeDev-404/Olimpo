<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;

class MasHerramientas extends Component
{
    protected $listeners = ['panelChanged' => '$refresh'];

    public function render()
    {
        return view('livewire.olimpo.mas-herramientas')
            ->layout('layouts.olimpo', ['title' => 'Más Herramientas']);
    }
}
