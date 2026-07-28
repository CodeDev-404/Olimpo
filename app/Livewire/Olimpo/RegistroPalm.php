<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;

class RegistroPalm extends Component
{
    protected $listeners = [];

    public function render()
    {
        return view('livewire.olimpo.registro-palm')
            ->layout('layouts.olimpo', ['title' => 'Registro PALM']);
    }
}
