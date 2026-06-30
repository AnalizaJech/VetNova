<?php

declare(strict_types=1);

namespace App\Livewire\Hospitalizacion;

use App\Models\Hospitalizacion;
use App\Models\HospitalizacionNota;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Hospitalización — VetNeoLink')]
class Index extends Component
{
    use AlertModal;

    // Modals
    public bool $modalIngreso = false;
    public bool $modalNotas = false;

    // Formulario Ingreso
    public ?int $mascota_id = null;
    public string $motivo_ingreso = '';
    public string $jaula = '';
    
    // Búsqueda asíncrona de pacientes
    public $mascotasSearch = [];

    // Formulario Notas
    public ?Hospitalizacion $hospActual = null;
    public string $nuevaNota = '';

    // Confirmación Alta
    public bool $modalAlta = false;
    public ?int $hospAltaId = null;

    public function buscarMascotas(string $value = '')
    {
        $this->mascotasSearch = Mascota::query()
            ->with('cliente')
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($value, function (Builder $query) use ($value) {
                $query->where('nombre', 'like', "%{$value}%")
                      ->orWhereHas('cliente', function ($q) use ($value) {
                          $q->where('nombres', 'like', "%{$value}%")
                            ->orWhere('numero_documento', 'like', "%{$value}%");
                      });
            })
            ->take(10)
            ->get();
    }

    public function abrirIngreso()
    {
        $this->reset(['mascota_id', 'motivo_ingreso', 'jaula']);
        $this->buscarMascotas('');
        $this->modalIngreso = true;
    }

    public function guardarIngreso()
    {
        $this->validate([
            'mascota_id' => 'required',
            'motivo_ingreso' => 'required|min:5',
        ]);

        Hospitalizacion::create([
            'clinica_id' => auth()->user()->clinica_id,
            'mascota_id' => $this->mascota_id,
            'jaula' => $this->jaula ?: null,
            'motivo_ingreso' => $this->motivo_ingreso,
            'fecha_ingreso' => now(),
            'estado' => 'INTERNADO',
        ]);

        $this->modalIngreso = false;
        $this->success('Paciente internado con éxito.', 'Ingreso Registrado');
    }

    public function verNotas(int $id)
    {
        $this->hospActual = Hospitalizacion::with(['mascota', 'notas.veterinario'])->findOrFail($id);
        $this->nuevaNota = '';
        $this->modalNotas = true;
    }

    public function guardarNota()
    {
        $this->validate([
            'nuevaNota' => 'required|min:3'
        ]);

        HospitalizacionNota::create([
            'hospitalizacion_id' => $this->hospActual->id,
            'veterinario_id' => auth()->id(),
            'nota' => $this->nuevaNota
        ]);

        $this->nuevaNota = '';
        // Recargar notas
        $this->hospActual->load('notas.veterinario');
        $this->success('Evolución clínica agregada.');
    }

    public function darDeAlta(int $id): void
    {
        $this->hospAltaId = $id;
        $this->modalAlta = true;
    }

    public function confirmarAlta(): void
    {
        if (!$this->hospAltaId) return;

        // Scope de clinica para proteger multi-tenant
        $hosp = Hospitalizacion::where('clinica_id', auth()->user()->clinica_id)->findOrFail($this->hospAltaId);
        
        $hosp->update([
            'estado' => 'DE_ALTA',
            'fecha_alta' => now(),
            'jaula' => null // Libera la jaula para otro paciente
        ]);

        $this->modalAlta = false;
        $this->hospAltaId = null;
        $this->success('Paciente dado de alta correctamente.', '¡Alta Exitosa!');
    }

    public function render()
    {
        $internados = Hospitalizacion::with(['mascota.cliente'])
            ->where('clinica_id', auth()->user()->clinica_id)
            ->where('estado', 'INTERNADO')
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return view('livewire.hospitalizacion.index', compact('internados'));
    }
}
