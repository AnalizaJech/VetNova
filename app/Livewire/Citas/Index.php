<?php

declare(strict_types=1);

namespace App\Livewire\Citas;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\User;
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
#[Title('Agenda de Citas — VetNova')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Filtros en tabla
    public string $search = '';
    public string $filtroFecha = ''; // YYYY-MM-DD
    public string $filtroEstado = '';

    // Modal
    public bool $modalModal = false;
    public bool $isEditing = false;

    // Formulario
    public ?int $cita_id = null;
    public ?int $cliente_id = null;
    public ?int $mascota_id = null;
    public ?int $veterinario_id = null;
    public string $fecha = '';
    public string $hora = '';
    public string $motivo = '';
    public string $estado = 'PENDIENTE';
    public string $notas = '';

    // Selectores UI
    public Collection|array $clientesSearch = [];
    public Collection|array $mascotasSelect = [];
    public Collection|array $veterinariosSelect = [];
    
    public array $estados = [
        ['id' => 'PENDIENTE', 'name' => 'Pendiente'],
        ['id' => 'CONFIRMADA', 'name' => 'Confirmada'],
        ['id' => 'EN_PROGRESO', 'name' => 'En Progreso'],
        ['id' => 'COMPLETADA', 'name' => 'Completada'],
        ['id' => 'CANCELADA', 'name' => 'Cancelada'],
        ['id' => 'NO_ASISTIO', 'name' => 'No Asistió'],
    ];

    public function mount(): void
    {
        $this->filtroFecha = now()->format('Y-m-d'); // Por defecto vemos las citas de hoy
        $this->buscarClientes('');
        $this->cargarVeterinarios();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroFecha(): void
    {
        $this->resetPage();
    }

    /**
     * Búsqueda asíncrona de clientes para Mary UI
     */
    public function buscarClientes(string $value = ''): void
    {
        $this->clientesSearch = Cliente::query()
            ->where('clinica_id', auth()->user()->clinica_id)
            ->where('activo', true)
            ->when($value, function (Builder $query) use ($value) {
                $query->where(function ($q) use ($value) {
                    $q->where('nombres', 'like', "%{$value}%")
                      ->orWhere('apellidos', 'like', "%{$value}%")
                      ->orWhere('numero_documento', 'like', "%{$value}%");
                });
            })
            ->take(15)
            ->get();
    }

    /**
     * Al seleccionar un cliente, cargamos sus mascotas dinámicamente
     */
    public function updatedClienteId($value): void
    {
        $this->mascota_id = null;
        if ($value) {
            $this->mascotasSelect = Mascota::where('cliente_id', $value)
                ->where('fallecido', false)
                ->get();
        } else {
            $this->mascotasSelect = collect();
        }
    }

    private function cargarVeterinarios(): void
    {
        // En un caso real, filtramos solo los usuarios con rol 'veterinario'
        // Por simplicidad en Fase 1, cargaremos a todos los usuarios de la clínica
        $this->veterinariosSelect = User::where('clinica_id', auth()->user()->clinica_id)
            ->where('activo', true)
            ->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        if (count($this->clientesSearch) === 0) {
            $this->buscarClientes('');
        }
        $this->modalModal = true;
    }

    public function edit(Cita $cita): void
    {
        $this->resetForm();
        $this->isEditing = true;
        
        $this->cita_id = $cita->id;
        $this->cliente_id = $cita->cliente_id;
        
        // Cargar cliente actual
        if ($cita->cliente) {
            $this->clientesSearch = collect([$cita->cliente]);
        }
        
        // Cargar mascotas del cliente
        $this->updatedClienteId($this->cliente_id);
        
        $this->mascota_id = $cita->mascota_id;
        $this->veterinario_id = $cita->veterinario_id;
        
        $this->fecha = $cita->fecha_hora->format('Y-m-d');
        $this->hora = $cita->fecha_hora->format('H:i');
        
        $this->motivo = $cita->motivo;
        $this->estado = $cita->estado;
        $this->notas = $cita->notas ?? '';

        $this->modalModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'veterinario_id' => 'nullable|exists:users,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'motivo' => 'required|string|max:150',
            'estado' => 'required|string',
        ], [
            'mascota_id.required' => 'Debes seleccionar una mascota paciente.',
        ]);

        $clinica_id = auth()->user()->clinica_id;
        $fecha_hora = Carbon::parse("{$this->fecha} {$this->hora}");

        // Validación simple de cruce de horarios para el mismo veterinario
        if ($this->veterinario_id && in_array($this->estado, ['PENDIENTE', 'CONFIRMADA'])) {
            $cruce = Cita::where('clinica_id', $clinica_id)
                ->where('veterinario_id', $this->veterinario_id)
                ->where('fecha_hora', $fecha_hora)
                ->whereIn('estado', ['PENDIENTE', 'CONFIRMADA'])
                ->when($this->cita_id, fn($q) => $q->where('id', '!=', $this->cita_id))
                ->exists();

            if ($cruce) {
                $this->addError('hora', 'El veterinario ya tiene una cita reservada a esta hora exacta.');
                return;
            }
        }

        $data = [
            'clinica_id' => $clinica_id,
            'cliente_id' => $this->cliente_id,
            'mascota_id' => $this->mascota_id,
            'veterinario_id' => $this->veterinario_id,
            'fecha_hora' => $fecha_hora,
            'motivo' => $this->motivo,
            'estado' => $this->estado,
            'notas' => $this->notas,
        ];

        if ($this->isEditing && $this->cita_id) {
            Cita::where('clinica_id', $clinica_id)->findOrFail($this->cita_id)->update($data);
            $this->success('Cita actualizada correctamente.');
        } else {
            Cita::create($data);
            $this->success('Cita registrada correctamente.');
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    public function cambiarEstado(int $id, string $nuevoEstado): void
    {
        $cita = Cita::where('clinica_id', auth()->user()->clinica_id)->findOrFail($id);
        $cita->update(['estado' => $nuevoEstado]);
        $this->success("Estado actualizado a {$nuevoEstado}.");
    }

    private function resetForm(): void
    {
        $this->reset([
            'cita_id', 'cliente_id', 'mascota_id', 'veterinario_id', 'motivo', 'notas'
        ]);
        $this->fecha = now()->format('Y-m-d');
        $this->hora = now()->addHour()->startOfHour()->format('H:i'); // Sugiere la próxima hora en punto
        $this->estado = 'PENDIENTE';
        $this->mascotasSelect = collect();
    }

    // Configuración de tabla Mary UI
    public function headers(): array
    {
        return [
            ['key' => 'horario', 'label' => 'Horario', 'class' => 'w-32'],
            ['key' => 'paciente', 'label' => 'Paciente'],
            ['key' => 'motivo', 'label' => 'Motivo y Vet.', 'class' => 'hidden md:table-cell'],
            ['key' => 'estado', 'label' => 'Estado', 'class' => 'w-32'],
        ];
    }

    public function getCitasProperty(): LengthAwarePaginator
    {
        return Cita::with(['cliente', 'mascota', 'veterinario'])
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($this->filtroFecha, function (Builder $query) {
                $query->whereDate('fecha_hora', $this->filtroFecha);
            })
            ->when($this->filtroEstado, function (Builder $query) {
                $query->where('estado', $this->filtroEstado);
            })
            ->when($this->search, function (Builder $query) {
                // Envolver en where() para proteger el scope de clinica_id
                $query->where(function ($outer) {
                    $outer->whereHas('cliente', function ($q) {
                        $q->where('nombres', 'like', "%{$this->search}%")
                          ->orWhere('apellidos', 'like', "%{$this->search}%");
                    })->orWhereHas('mascota', function ($q) {
                        $q->where('nombre', 'like', "%{$this->search}%");
                    })->orWhere('motivo', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('fecha_hora', 'asc')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.citas.index', [
            'citas' => $this->citas,
            'headers' => $this->headers(),
        ]);
    }
}


