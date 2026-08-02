<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;
use Livewire\Attributes\Computed;

class RegistroPalm extends Component
{
    public $search = '';
    public $selectMode = false;
    public $selectedIds = [];

    protected $listeners = [];

    #[Computed]
    public function getRegistrosProperty()
    {
        return [];
    }

    #[Computed]
    public function getRegistrosHoyProperty()
    {
        return 0;
    }

    public function toggleSelectMode()
    {
        $this->selectMode = !$this->selectMode;
        if (!$this->selectMode) $this->selectedIds = [];
    }

    public function toggleSelect($id)
    {
        $key = array_search($id, $this->selectedIds);
        if ($key !== false) {
            unset($this->selectedIds[$key]);
        } else {
            $this->selectedIds[] = $id;
        }
        $this->selectedIds = array_values($this->selectedIds);
    }

    public function toggleSelectAll()
    {
        $currentIds = array_map('intval', collect($this->registros)->pluck('id')->toArray());
        $current = array_values(array_unique(array_map('intval', $this->selectedIds)));
        $allSelected = $currentIds && !array_diff($currentIds, $current);
        if ($allSelected) {
            $this->selectedIds = array_values(array_diff($current, $currentIds));
        } else {
            $this->selectedIds = array_values(array_unique(array_merge($current, $currentIds)));
        }
    }

    public function render()
    {
        return view('livewire.olimpo.registro-palm')
            ->layout('layouts.olimpo', ['title' => 'Registro PALM']);
    }
}
