<?php

namespace App\Livewire\Olimpo;

use Livewire\Component;

class Sidebar extends Component
{
    public array $navItems = [];

    public function mount()
    {
        $svg = function($p) {
            $icons = [
                'dashboard' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                'ocurrencias' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
                'asistencia' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                'personal' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                'recordatorios' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
                'cumpleanos' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A2.701 2.701 0 003 15.546M21 15.546V9.75a2.25 2.25 0 00-2.25-2.25h-13.5A2.25 2.25 0 003 9.75v5.796M21 15.546V21H3v-5.454M12 3v2.25M9 3h.008M15 3h.008"/></svg>',
                'registro-palm' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'control-camionetas' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
                'mas-herramientas' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
                'consultas-dni' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                'config' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            ];
            return $icons[$p] ?? '';
        };

        $this->navItems = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'svg' => $svg('dashboard'), 'route' => 'olimpo.dashboard'],
            ['key' => 'ocurrencias', 'label' => 'Ocurrencias', 'svg' => $svg('ocurrencias'), 'route' => 'olimpo.ocurrencias'],
            ['key' => 'asistencia', 'label' => 'Asistencia', 'svg' => $svg('asistencia'), 'route' => 'olimpo.asistencia'],
            ['key' => 'personal', 'label' => 'Personal', 'svg' => $svg('personal'), 'route' => 'olimpo.personal'],
            ['key' => 'recordatorios', 'label' => 'Recordatorios', 'svg' => $svg('recordatorios'), 'route' => 'olimpo.recordatorios'],
            ['key' => 'cumpleanos', 'label' => 'Cumpleaños', 'svg' => $svg('cumpleanos'), 'route' => 'olimpo.cumpleanos'],
            ['key' => 'registro-palm', 'label' => 'Registro PALM', 'svg' => $svg('registro-palm'), 'route' => 'olimpo.registro-palm'],
            ['key' => 'control-camionetas', 'label' => 'Control Vehículos', 'svg' => $svg('control-camionetas'), 'route' => 'olimpo.control-camionetas'],
            ['key' => 'consultas-dni', 'label' => 'Consultas DNI / RUC', 'svg' => $svg('consultas-dni'), 'route' => 'olimpo.consultas-dni'],
            ['key' => 'mas-herramientas', 'label' => 'Más Herramientas', 'svg' => $svg('mas-herramientas'), 'route' => 'olimpo.mas-herramientas'],
            ['key' => 'config', 'label' => 'Configuración', 'svg' => $svg('config'), 'route' => 'olimpo.config'],
        ];

        // Hide admin-only items from non-admin users
        if (auth()->user()?->role !== 'admin') {
            $this->navItems = array_values(array_filter($this->navItems, fn($i) => $i['key'] !== 'consultas-dni'));
        }
    }

    protected $listeners = [];

    public function render()
    {
        $currentRoute = request()->route()?->getName();
        return view('livewire.olimpo.sidebar', [
            'currentRoute' => $currentRoute,
        ]);
    }
}
