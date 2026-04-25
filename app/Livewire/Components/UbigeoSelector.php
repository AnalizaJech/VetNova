<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\UbigeoDepartamento;
use App\Models\UbigeoDistrito;
use App\Models\UbigeoProvincia;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Componente Livewire para seleccionar Ubigeo (Departamento -> Provincia -> Distrito).
 * Carga los datos desde la BD local.
 */
class UbigeoSelector extends Component
{
    public ?int $departamento_id = null;
    public ?int $provincia_id = null;
    public ?int $distrito_id = null;

    public $departamentos = [];
    public $provincias = [];
    public $distritos = [];

    public function mount(?int $distrito_id = null): void
    {
        $this->departamentos = UbigeoDepartamento::orderBy('nombre')->get();

        if ($distrito_id) {
            $this->distrito_id = $distrito_id;
            $distrito = UbigeoDistrito::with('provincia')->find($distrito_id);
            if ($distrito) {
                $this->provincia_id = $distrito->provincia_id;
                $this->departamento_id = $distrito->provincia->departamento_id;

                $this->provincias = UbigeoProvincia::where('departamento_id', $this->departamento_id)
                    ->orderBy('nombre')
                    ->get();
                $this->distritos = UbigeoDistrito::where('provincia_id', $this->provincia_id)
                    ->orderBy('nombre')
                    ->get();
            }
        }
    }

    public function updatedDepartamentoId($value): void
    {
        $this->provincia_id = null;
        $this->distrito_id = null;
        $this->distritos = [];
        $this->provincias = UbigeoProvincia::where('departamento_id', $value)->orderBy('nombre')->get();
        $this->dispatch('ubigeo-updated', null);
    }

    public function updatedProvinciaId($value): void
    {
        $this->distrito_id = null;
        $this->distritos = UbigeoDistrito::where('provincia_id', $value)->orderBy('nombre')->get();
        $this->dispatch('ubigeo-updated', null);
    }

    public function updatedDistritoId($value): void
    {
        $this->dispatch('ubigeo-updated', $value);
    }

    /**
     * Escucha el evento 'preseleccionarUbigeo' emitido por RUC autocomplete.
     */
    #[On('preseleccionarUbigeo')]
    public function preseleccionarPorCodigo(string $codigo_ubigeo): void
    {
        $distrito = UbigeoDistrito::with('provincia')->where('codigo_ubigeo', $codigo_ubigeo)->first();
        if ($distrito) {
            $this->departamento_id = $distrito->provincia->departamento_id;
            $this->provincias = UbigeoProvincia::where('departamento_id', $this->departamento_id)
                ->orderBy('nombre')
                ->get();
            
            $this->provincia_id = $distrito->provincia_id;
            $this->distritos = UbigeoDistrito::where('provincia_id', $this->provincia_id)
                ->orderBy('nombre')
                ->get();
            
            $this->distrito_id = $distrito->id;
            $this->dispatch('ubigeo-updated', $this->distrito_id);
        }
    }

    public function render()
    {
        return view('livewire.components.ubigeo-selector');
    }
}


