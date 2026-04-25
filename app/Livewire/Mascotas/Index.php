<?php

declare(strict_types=1);

namespace App\Livewire\Mascotas;

use App\Models\Cliente;
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
#[Title('Mascotas — VetNova')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Búsqueda en tabla
    public string $search = '';

    // Modal
    public bool $modalModal = false;
    public bool $isEditing = false;

    // Formulario Mascota
    public ?int $mascota_id = null;
    public ?int $cliente_id = null;
    public string $nombre = '';
    public string $especie = 'Perro';
    public string $raza = '';
    public string $sexo = 'M';
    public string $color = '';
    public ?string $fecha_nacimiento = null;
    public ?float $peso_actual = null;
    public bool $esterilizado = false;
    public bool $fallecido = false;
    public string $notas_medicas = '';

    // Selector asíncrono de clientes (Mary UI)
    public Collection|array $clientesSearch = [];

    // Opciones estáticas
    public array $especies = [
        ['id' => 'Perro', 'name' => 'Perro'],
        ['id' => 'Gato', 'name' => 'Gato'],
        ['id' => 'Ave', 'name' => 'Ave'],
        ['id' => 'Roedor', 'name' => 'Roedor'],
        ['id' => 'Exótico', 'name' => 'Exótico'],
        ['id' => 'Otro', 'name' => 'Otro'],
    ];

    public array $sexos = [
        ['id' => 'M', 'name' => 'Macho'],
        ['id' => 'H', 'name' => 'Hembra'],
    ];

    public function mount(): void
    {
        // Cargar algunos clientes iniciales para el selector
        $this->buscarClientes('');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Función llamada asíncronamente por el <x-choices> de Mary UI
     * para buscar clientes por nombre o documento sin recargar la página.
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

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        // Si no hay clientes cargados, forzar carga inicial
        if (count($this->clientesSearch) === 0) {
            $this->buscarClientes('');
        }
        $this->modalModal = true;
    }

    public function edit(Mascota $mascota): void
    {
        $this->resetForm();
        $this->isEditing = true;
        
        $this->mascota_id = $mascota->id;
        $this->cliente_id = $mascota->cliente_id;
        
        // Asegurarnos de que el cliente actual esté en la lista del selector
        if ($mascota->cliente) {
            $this->clientesSearch = collect([$mascota->cliente]);
        }

        $this->nombre = $mascota->nombre;
        $this->especie = $mascota->especie;
        $this->raza = $mascota->raza ?? '';
        $this->sexo = $mascota->sexo;
        $this->color = $mascota->color ?? '';
        $this->fecha_nacimiento = $mascota->fecha_nacimiento?->format('Y-m-d');
        $this->peso_actual = (float) $mascota->peso_actual;
        $this->esterilizado = $mascota->esterilizado;
        $this->fallecido = $mascota->fallecido;
        $this->notas_medicas = $mascota->notas_medicas ?? '';

        $this->modalModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'nombre' => 'required|string|max:100',
            'especie' => 'required|string|max:50',
            'raza' => 'nullable|string|max:100',
            'sexo' => 'required|in:M,H',
            'color' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:today',
            'peso_actual' => 'nullable|numeric|min:0|max:500',
        ], [
            'cliente_id.required' => 'Debes seleccionar un cliente propietario.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ]);

        $clinica_id = auth()->user()->clinica_id;

        $data = [
            'clinica_id' => $clinica_id,
            'cliente_id' => $this->cliente_id,
            'nombre' => $this->nombre,
            'especie' => $this->especie,
            'raza' => $this->raza,
            'sexo' => $this->sexo,
            'color' => $this->color,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'peso_actual' => $this->peso_actual,
            'esterilizado' => $this->esterilizado,
            'fallecido' => $this->fallecido,
            'notas_medicas' => $this->notas_medicas,
        ];

        if ($this->isEditing && $this->mascota_id) {
            Mascota::where('clinica_id', $clinica_id)->findOrFail($this->mascota_id)->update($data);
            $this->success('Mascota actualizada correctamente.');
        } else {
            Mascota::create($data);
            $this->success('Mascota registrada correctamente.');
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $mascota = Mascota::where('clinica_id', auth()->user()->clinica_id)->findOrFail($id);
        $mascota->delete();
        $this->warning('Mascota eliminada del sistema.');
    }

    private function resetForm(): void
    {
        $this->reset([
            'mascota_id', 'cliente_id', 'nombre', 'raza', 'color', 
            'fecha_nacimiento', 'peso_actual', 'notas_medicas'
        ]);
        $this->especie = 'Perro';
        $this->sexo = 'M';
        $this->esterilizado = false;
        $this->fallecido = false;
    }

    // Configuración de la tabla Mary UI
    public function headers(): array
    {
        return [
            ['key' => 'mascota', 'label' => 'Mascota'],
            ['key' => 'detalles', 'label' => 'Detalles', 'class' => 'hidden md:table-cell'],
            ['key' => 'propietario', 'label' => 'Propietario'],
            ['key' => 'estado', 'label' => 'Estado', 'class' => 'w-24'],
        ];
    }

    public function getMascotasProperty(): LengthAwarePaginator
    {
        return Mascota::with('cliente')
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($this->search, function (Builder $query) {
                // Envolver en where() para no romper el scope de clinica_id con orWhereHas
                $query->where(function ($outer) {
                    $outer->where('nombre', 'like', "%{$this->search}%")
                          ->orWhereHas('cliente', function ($q) {
                              $q->where('nombres', 'like', "%{$this->search}%")
                                ->orWhere('apellidos', 'like', "%{$this->search}%")
                                ->orWhere('numero_documento', 'like', "%{$this->search}%");
                          });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.mascotas.index', [
            'mascotas' => $this->mascotas,
            'headers' => $this->headers(),
        ]);
    }
}


