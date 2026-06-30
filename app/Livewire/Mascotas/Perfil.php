<?php

declare(strict_types=1);

namespace App\Livewire\Mascotas;

use App\Models\Mascota;
use App\Models\HistoriaClinica;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Perfil del Paciente — VetNeoLink')]
class Perfil extends Component
{
    public Mascota $mascota;
    public $historias;
    public $recordatorios;

    public function mount(Mascota $mascota): void
    {
        // Seguridad: verificar que pertenece a la clínica
        if ($mascota->clinica_id !== auth()->user()->clinica_id) {
            abort(403);
        }

        $this->mascota = $mascota->load(['cliente']);
        $this->historias = HistoriaClinica::with(['veterinario', 'prescripciones'])
            ->where('mascota_id', $mascota->id)
            ->orderBy('fecha', 'desc')
            ->get();
            
        $this->recordatorios = \App\Models\RegistroPreventivo::where('mascota_id', $mascota->id)
            ->whereNotNull('fecha_proxima')
            ->orderBy('fecha_proxima', 'asc')
            ->get()
            ->map(fn($rp) => (object)[
                'titulo' => $rp->nombre,
                'descripcion' => "Próxima dosis de " . strtolower($rp->tipo),
                'fecha_aviso' => $rp->fecha_proxima,
                'completado' => $rp->fecha_proxima->isPast()
            ]);
    }

    public function render()
    {
        return view('livewire.mascotas.perfil');
    }
}
