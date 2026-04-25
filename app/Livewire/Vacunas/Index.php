<?php

declare(strict_types=1);

namespace App\Livewire\Vacunas;

use App\Models\Mascota;
use App\Models\RegistroPreventivo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Vacunas y Desparasitaciones — VetNova')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Filtros
    public string $search = '';
    public string $filtroTipo = '';

    // Modal
    public bool $modalModal = false;
    public bool $isEditing = false;

    // Formulario
    public ?int $registro_id = null;
    public ?int $mascota_id = null;
    public string $tipo = 'VACUNA';
    public string $producto_o_enfermedad = '';
    public string $lote_marca = '';
    public ?float $peso_al_momento = null;
    public string $fecha_aplicacion = '';
    public ?string $fecha_proxima = null;
    public string $notas = '';

    // Selector asíncrono
    public Collection|array $mascotasSearch = [];

    public array $tipos = [
        ['id' => 'VACUNA', 'name' => 'Vacunación'],
        ['id' => 'DESPARASITACION_INT', 'name' => 'Desparasitación Interna'],
        ['id' => 'DESPARASITACION_EXT', 'name' => 'Desparasitación Externa'],
    ];

    public function mount(): void
    {
        $this->buscarMascotas('');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

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
                            ->orWhere('apellidos', 'like', "%{$value}%");
                      });
            })
            ->take(15)
            ->get();
    }

    public function setProximaFecha(int $meses): void
    {
        if ($this->fecha_aplicacion) {
            $this->fecha_proxima = Carbon::parse($this->fecha_aplicacion)->addMonthsNoOverflow($meses)->format('Y-m-d');
        } else {
            $this->fecha_proxima = Carbon::now()->addMonthsNoOverflow($meses)->format('Y-m-d');
        }
    }

    public function create(?int $mascota_id = null): void
    {
        $this->resetForm();
        $this->isEditing = false;
        
        if ($mascota_id) {
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

    public function edit(RegistroPreventivo $registro): void
    {
        $this->resetForm();
        $this->isEditing = true;
        
        $this->registro_id = $registro->id;
        $this->mascota_id = $registro->mascota_id;
        
        if ($registro->mascota) {
            $this->mascotasSearch = collect([$registro->mascota]);
        }

        $this->tipo = $registro->tipo;
        $this->producto_o_enfermedad = $registro->producto_o_enfermedad;
        $this->lote_marca = $registro->lote_marca ?? '';
        $this->peso_al_momento = $registro->peso_al_momento ? (float) $registro->peso_al_momento : null;
        
        $this->fecha_aplicacion = $registro->fecha_aplicacion->format('Y-m-d');
        $this->fecha_proxima = $registro->fecha_proxima?->format('Y-m-d');
        $this->notas = $registro->notas ?? '';

        $this->modalModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'tipo' => 'required|in:VACUNA,DESPARASITACION_INT,DESPARASITACION_EXT',
            'producto_o_enfermedad' => 'required|string|max:150',
            'lote_marca' => 'nullable|string|max:100',
            'peso_al_momento' => 'nullable|numeric|min:0|max:500',
            'fecha_aplicacion' => 'required|date',
            'fecha_proxima' => 'nullable|date|after_or_equal:fecha_aplicacion',
        ]);

        $clinica_id = auth()->user()->clinica_id;

        $data = [
            'clinica_id' => $clinica_id,
            'mascota_id' => $this->mascota_id,
            'veterinario_id' => auth()->id(), // Quien registra la vacuna
            'tipo' => $this->tipo,
            'producto_o_enfermedad' => $this->producto_o_enfermedad,
            'lote_marca' => $this->lote_marca,
            'peso_al_momento' => $this->peso_al_momento,
            'fecha_aplicacion' => $this->fecha_aplicacion,
            'fecha_proxima' => $this->fecha_proxima,
            'notas' => $this->notas,
        ];

        if ($this->isEditing && $this->registro_id) {
            RegistroPreventivo::where('clinica_id', $clinica_id)->findOrFail($this->registro_id)->update($data);
            $this->success('Registro actualizado correctamente.');
        } else {
            RegistroPreventivo::create($data);
            $this->success('Vacuna / Desparasitación registrada.');
        }

        // AUTO-ACTUALIZACIÓN de peso de la mascota si es proporcionado
        if ($this->peso_al_momento !== null) {
            $mascota = Mascota::find($this->mascota_id);
            if ($mascota && (float)$mascota->peso_actual !== (float)$this->peso_al_momento) {
                $mascota->update(['peso_actual' => $this->peso_al_momento]);
            }
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $registro = RegistroPreventivo::where('clinica_id', auth()->user()->clinica_id)->findOrFail($id);
        $registro->delete();
        $this->warning('Registro eliminado correctamente.');
    }

    private function resetForm(): void
    {
        $this->reset([
            'registro_id', 'mascota_id', 'producto_o_enfermedad', 'lote_marca',
            'peso_al_momento', 'fecha_proxima', 'notas'
        ]);
        $this->tipo = 'VACUNA';
        $this->fecha_aplicacion = now()->format('Y-m-d');
    }

    // Configuración de tabla Mary UI
    public function headers(): array
    {
        return [
            ['key' => 'fecha', 'label' => 'Aplicación', 'class' => 'w-32'],
            ['key' => 'paciente', 'label' => 'Paciente'],
            ['key' => 'detalle', 'label' => 'Producto / Detalle'],
            ['key' => 'proxima', 'label' => 'Próxima Dosis', 'class' => 'hidden md:table-cell'],
        ];
    }

    public function getRegistrosProperty(): LengthAwarePaginator
    {
        return RegistroPreventivo::with(['mascota.cliente'])
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($this->filtroTipo, function (Builder $query) {
                $query->where('tipo', $this->filtroTipo);
            })
            ->when($this->search, function (Builder $query) {
                $query->where(function ($q) {
                    $q->whereHas('mascota', function ($q2) {
                        $q2->where('nombre', 'like', "%{$this->search}%")
                           ->orWhereHas('cliente', function ($q3) {
                               $q3->where('nombres', 'like', "%{$this->search}%");
                           });
                    })->orWhere('producto_o_enfermedad', 'like', "%{$this->search}%")
                      ->orWhere('lote_marca', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('fecha_aplicacion', 'desc')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.vacunas.index', [
            'registros' => $this->registros,
            'headers' => $this->headers(),
        ]);
    }
}


