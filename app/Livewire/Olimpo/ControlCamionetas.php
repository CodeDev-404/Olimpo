<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use App\Models\Camioneta;

class ControlCamionetas extends Component
{
    public $camionetas = [];

    protected $listeners = ['panelChanged' => 'loadCamionetas'];

    public function mount()
    {
        $this->loadCamionetas();
    }

    public function loadCamionetas()
    {
        $this->camionetas = Camioneta::orderBy('placa')->get()->toArray();
    }

    public function render()
    {
        return view('livewire.olimpo.control-camionetas')
            ->layout('layouts.olimpo', ['title' => 'Control Vehículos']);
    }
}
