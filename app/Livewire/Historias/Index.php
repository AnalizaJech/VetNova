<?php

declare(strict_types=1);

namespace App\Livewire\Historias;

use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Historias Clínicas — VetNova')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Búsqueda
    public string $search = '';

    // Modal
    public bool $modalModal = false;
    public bool $isEditing = false;

    // Formulario
    public ?int $historia_id = null;
    public ?int $mascota_id = null;
    public ?int $cita_id = null; // Opcional, si viene de la agenda
    public string $fecha = '';
    public string $hora = '';
    public string $motivo_consulta = '';
    public ?float $peso = null;
    public ?float $temperatura = null;
    public ?int $frecuencia_cardiaca = null;
    public ?int $frecuencia_respiratoria = null;
    public string $anamnesis = '';
    public string $diagnostico_presuntivo = '';
    public string $tratamiento_indicaciones = '';
    public ?string $proxima_cita_recomendada = null;

    // Selectores asíncronos (Mary UI)
    public Collection|array $mascotasSearch = [];

    public function mount(): void
    {
        $this->buscarMascotas('');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Búsqueda asíncrona de pacientes (Mascotas)
     */
    public function buscarMascotas(string $value = ''): void
    {
        $this->mascotasSearch = Mascota::query()
            ->with('cliente')
            ->where('clinica_id', auth()->user()->clinica_id)
            ->where('fallecido', false)
            ->when($value, function (Builder $query) use ($value) {
                $query->where('nombre', 'like', "%{$value}%")
                      ->orWhereHas('cliente', function ($q) use ($value) {
                          $q->where('nombres', 'like', "%{$value}%")
                            ->orWhere('apellidos', 'like', "%{$value}%")
                            ->orWhere('numero_documento', 'like', "%{$value}%");
                      });
            })
            ->take(15)
            ->get();
    }

    public function create(?int $cita_id = null, ?int $mascota_id = null): void
    {
        $this->resetForm();
        $this->isEditing = false;
        
        if ($cita_id && $mascota_id) {
            $this->cita_id = $cita_id;
            $this->mascota_id = $mascota_id;
            $mascota = Mascota::find($mascota_id);
            if ($mascota) {
                $this->mascotasSearch = collect([$mascota]);
            }
        } elseif (count($this->mascotasSearch) === 0) {
            $this->buscarMascotas('');
        }
        
        $this->modalModal = true;
    }

    public function edit(HistoriaClinica $historia): void
    {
        $this->resetForm();
        $this->isEditing = true;
        
        $this->historia_id = $historia->id;
        $this->mascota_id = $historia->mascota_id;
        $this->cita_id = $historia->cita_id;
        
        if ($historia->mascota) {
            $this->mascotasSearch = collect([$historia->mascota]);
        }

        $this->fecha = $historia->fecha->format('Y-m-d');
        $this->hora = $historia->fecha->format('H:i');
        
        $this->motivo_consulta = $historia->motivo_consulta;
        $this->peso = $historia->peso !== null ? (float) $historia->peso : null;
        $this->temperatura = $historia->temperatura !== null ? (float) $historia->temperatura : null;
        $this->frecuencia_cardiaca = $historia->frecuencia_cardiaca;
        $this->frecuencia_respiratoria = $historia->frecuencia_respiratoria;
        $this->anamnesis = $historia->anamnesis ?? '';
        $this->diagnostico_presuntivo = $historia->diagnostico_presuntivo ?? '';
        $this->tratamiento_indicaciones = $historia->tratamiento_indicaciones ?? '';
        $this->proxima_cita_recomendada = $historia->proxima_cita_recomendada?->format('Y-m-d');

        $this->modalModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'motivo_consulta' => 'required|string|max:255',
            'peso' => 'nullable|numeric|min:0|max:500',
            'temperatura' => 'nullable|numeric|min:20|max:50',
            'frecuencia_cardiaca' => 'nullable|integer|min:0|max:300',
            'frecuencia_respiratoria' => 'nullable|integer|min:0|max:150',
            'anamnesis' => 'nullable|string',
            'diagnostico_presuntivo' => 'nullable|string',
            'tratamiento_indicaciones' => 'nullable|string',
            'proxima_cita_recomendada' => 'nullable|date|after_or_equal:today',
        ], [
            'mascota_id.required' => 'Debes seleccionar el paciente para esta consulta.',
        ]);

        $clinica_id = auth()->user()->clinica_id;
        $fechaHora = \Carbon\Carbon::parse("{$this->fecha} {$this->hora}");

        $data = [
            'clinica_id' => $clinica_id,
            'mascota_id' => $this->mascota_id,
            'veterinario_id' => auth()->id(), // El veterinario que registra
            'cita_id' => $this->cita_id,
            'fecha' => $fechaHora,
            'motivo_consulta' => $this->motivo_consulta,
            'peso' => $this->peso,
            'temperatura' => $this->temperatura,
            'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
            'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
            'anamnesis' => $this->anamnesis,
            'diagnostico_presuntivo' => $this->diagnostico_presuntivo,
            'tratamiento_indicaciones' => $this->tratamiento_indicaciones,
            'proxima_cita_recomendada' => $this->proxima_cita_recomendada,
        ];

        if ($this->isEditing && $this->historia_id) {
            HistoriaClinica::where('clinica_id', $clinica_id)->findOrFail($this->historia_id)->update($data);
            $this->success('Consulta actualizada correctamente.');
        } else {
            HistoriaClinica::create($data);
            
            // Si la consulta viene de una cita, completarla
            if ($this->cita_id) {
                Cita::where('clinica_id', $clinica_id)->where('id', $this->cita_id)->update(['estado' => 'COMPLETADA']);
            }
            
            $this->success('Consulta registrada exitosamente.');
        }

        // AUTO-ACTUALIZACIÓN: Actualizar peso actual de la mascota
        if ($this->peso !== null) {
            $mascota = Mascota::find($this->mascota_id);
            if ($mascota && (float)$mascota->peso_actual !== (float)$this->peso) {
                $mascota->update(['peso_actual' => $this->peso]);
            }
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'historia_id', 'mascota_id', 'cita_id', 'motivo_consulta', 'peso',
            'temperatura', 'frecuencia_cardiaca', 'frecuencia_respiratoria',
            'anamnesis', 'diagnostico_presuntivo', 'tratamiento_indicaciones', 'proxima_cita_recomendada'
        ]);
        $this->fecha = now()->format('Y-m-d');
        $this->hora = now()->format('H:i');
    }

    // Configuración de tabla Mary UI
    public function headers(): array
    {
        return [
            ['key' => 'fecha', 'label' => 'Fecha', 'class' => 'w-32'],
            ['key' => 'paciente', 'label' => 'Paciente'],
            ['key' => 'motivo', 'label' => 'Motivo / Triage'],
            ['key' => 'veterinario', 'label' => 'Atendió', 'class' => 'hidden md:table-cell'],
        ];
    }

    public function getHistoriasProperty(): LengthAwarePaginator
    {
        return HistoriaClinica::with(['mascota.cliente', 'veterinario'])
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($this->search, function (Builder $query) {
                $query->whereHas('mascota', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhereHas('cliente', function ($q2) {
                          $q2->where('nombres', 'like', "%{$this->search}%")
                             ->orWhere('apellidos', 'like', "%{$this->search}%");
                      });
                })->orWhere('motivo_consulta', 'like', "%{$this->search}%");
            })
            ->orderBy('fecha', 'desc')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.historias.index', [
            'historias' => $this->historias,
            'headers' => $this->headers(),
        ]);
    }
}


