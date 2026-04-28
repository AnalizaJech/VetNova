<?php

declare(strict_types=1);

namespace App\Livewire\Historias;

use App\Models\Cita;
use App\Models\HistoriaClinica;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
    #[\Livewire\Attributes\Url]
    public string $search = '';
    public string $filtroFecha = '';

    // Modal Visor Completo
    public bool $modalVer = false;
    public ?HistoriaClinica $historiaSeleccionada = null;

    // Modal Formulario
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

    // Prescripciones
    public array $prescripciones = [];
    public string $prescripcion_medicamento = '';
    public string $prescripcion_dosis = '';
    public string $prescripcion_frecuencia = '';
    public string $prescripcion_duracion = '';

    public function agregarPrescripcion(): void
    {
        $this->validate([
            'prescripcion_medicamento' => 'required|string|max:100',
            'prescripcion_dosis' => 'required|string|max:100',
        ], [
            'prescripcion_medicamento.required' => 'Indique el nombre del medicamento.',
            'prescripcion_dosis.required' => 'Indique la dosis.',
        ]);

        $this->prescripciones[] = [
            'medicamento' => $this->prescripcion_medicamento,
            'dosis' => $this->prescripcion_dosis,
            'frecuencia' => $this->prescripcion_frecuencia,
            'duracion' => $this->prescripcion_duracion,
        ];

        $this->reset(['prescripcion_medicamento', 'prescripcion_dosis', 'prescripcion_frecuencia', 'prescripcion_duracion']);
    }

    public function quitarPrescripcion(int $index): void
    {
        unset($this->prescripciones[$index]);
        $this->prescripciones = array_values($this->prescripciones);
    }

    // Selectores asíncronos (Mary UI)
    public Collection|array $mascotasSearch = [];

    public function mount(): void
    {
        // Detectar si viene desde la agenda de citas
        $citaId   = request()->query('cita_id');
        $mascotaId = request()->query('mascota_id');
        $fromAgenda = request()->query('from') === 'agenda';

        $this->buscarMascotas('');

        if ($citaId && $mascotaId && $fromAgenda) {
            // Abrir directamente el modal de nueva consulta con contexto pre-cargado
            $this->create((int)$citaId, (int)$mascotaId);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function verCompleto(int $id): void
    {
        $this->historiaSeleccionada = HistoriaClinica::with(['mascota.cliente', 'veterinario'])->findOrFail($id);
        $this->modalVer = true;
    }

    /**
     * Búsqueda asíncrona de pacientes (Mascotas)
     */
    public function buscarMascotas(string $value = ''): void
    {
        $this->mascotasSearch = Mascota::query()
            ->with('cliente')
            ->where('clinica_id', Auth::user()->clinica_id)
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
            
            $cita = Cita::find($cita_id);
            if ($cita) {
                $this->motivo_consulta = $cita->motivo;
            }

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

        // Cargar prescripciones existentes
        $this->prescripciones = $historia->prescripciones->map(fn($p) => [
            'medicamento' => $p->medicamento,
            'dosis' => $p->dosis,
            'frecuencia' => $p->frecuencia,
            'duracion' => $p->duracion,
        ])->toArray();

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

        $clinica_id = Auth::user()->clinica_id;

        if (!$this->isEditing && $this->cita_id) {
            $yaExiste = HistoriaClinica::where('cita_id', $this->cita_id)->exists();
            if ($yaExiste) {
                $this->error('Ya existe una historia clínica para esta cita. Edítala desde la tabla.');
                return;
            }
        }

        $fechaHora = \Carbon\Carbon::parse("{$this->fecha} {$this->hora}");

        $data = [
            'clinica_id' => $clinica_id,
            'mascota_id' => $this->mascota_id,
            'veterinario_id' => Auth::id(), // El veterinario que registra
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
            $historiaActual = HistoriaClinica::where('clinica_id', $clinica_id)->findOrFail($this->historia_id);
            $historiaActual->update($data);
            $historiaActual->prescripciones()->delete();
            $this->success('Consulta actualizada correctamente.');
        } else {
            $historiaActual = HistoriaClinica::create($data);
            
            // Si la consulta viene de una cita, completarla
            if ($this->cita_id) {
                Cita::where('clinica_id', $clinica_id)->where('id', $this->cita_id)->update(['estado' => 'COMPLETADA']);
            }
            
            $this->success('Consulta registrada exitosamente.');
        }

        // Guardar Prescripciones
        if ($historiaActual && !empty($this->prescripciones)) {
            foreach ($this->prescripciones as $p) {
                \App\Models\Prescripcion::create([
                    'historia_clinica_id' => $historiaActual->id,
                    'medicamento' => $p['medicamento'],
                    'dosis' => $p['dosis'],
                    'frecuencia' => $p['frecuencia'] ?? null,
                    'duracion' => $p['duracion'] ?? null,
                ]);
            }
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
            'anamnesis', 'diagnostico_presuntivo', 'tratamiento_indicaciones', 'proxima_cita_recomendada',
            'prescripciones', 'prescripcion_medicamento', 'prescripcion_dosis', 'prescripcion_frecuencia', 'prescripcion_duracion'
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
            ->where('clinica_id', Auth::user()->clinica_id)
            ->when($this->filtroFecha, fn($q) => $q->whereDate('fecha', $this->filtroFecha))
            ->when($this->search, function (Builder $query) {
                // Envolver en where() para proteger el scope de clinica_id
                $query->where(function ($outer) {
                    $outer->whereHas('mascota', function ($q) {
                        $q->where('nombre', 'like', "%{$this->search}%")
                          ->orWhereHas('cliente', function ($q2) {
                              $q2->where('nombres', 'like', "%{$this->search}%")
                                 ->orWhere('apellidos', 'like', "%{$this->search}%");
                          });
                    })->orWhere('motivo_consulta', 'like', "%{$this->search}%");
                });
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


