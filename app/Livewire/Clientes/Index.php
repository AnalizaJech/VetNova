<?php

declare(strict_types=1);

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use App\Models\UbigeoDistrito;
use App\Services\PeruApiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Clientes — VetNeoLink')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Búsqueda
    public string $search = '';

    // Modal
    public bool $modalModal = false;
    public bool $isEditing = false;

    // Formulario
    public ?int $cliente_id = null;
    public string $tipo_documento = 'DNI';
    public string $numero_documento = '';
    public string $nombres = '';
    public string $apellidos = '';
    public string $email = '';
    public string $telefono = '';
    public string $direccion = '';
    public ?int $distrito_id = null;
    public ?string $codigo_ubigeo = null;
    public string $notas = '';
    public bool $activo = true;

    // Estado UI
    public bool $buscandoApi = false;

    // Tipos de documento
    public array $tiposDocumento = [
        ['id' => 'DNI', 'name' => 'DNI'],
        ['id' => 'RUC', 'name' => 'RUC'],
        ['id' => 'CE', 'name' => 'Carné de Extranjería'],
        ['id' => 'PASAPORTE', 'name' => 'Pasaporte'],
    ];

    // Resetear paginación al buscar
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Escucha el evento emitido por el componente UbigeoSelector
     */
    #[On('ubigeo-updated')]
    public function updateDistritoId(?int $distrito_id): void
    {
        $this->distrito_id = $distrito_id;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->modalModal = true;
    }

    public function edit(Cliente $cliente): void
    {
        $this->resetForm();
        $this->isEditing = true;
        
        $this->cliente_id = $cliente->id;
        $this->tipo_documento = $cliente->tipo_documento;
        $this->numero_documento = $cliente->numero_documento;
        $this->nombres = $cliente->nombres;
        $this->apellidos = $cliente->apellidos ?? '';
        $this->email = $cliente->email ?? '';
        $this->telefono = $cliente->telefono ?? '';
        $this->direccion = $cliente->direccion ?? '';
        $this->distrito_id = $cliente->distrito_id;
        $this->codigo_ubigeo = $cliente->codigo_ubigeo;
        $this->notas = $cliente->notas ?? '';
        $this->activo = $cliente->activo;

        $this->modalModal = true;
    }

    /**
     * Busca DNI o RUC en la API (PeruAPI)
     */
    public function buscarEnApi(PeruApiService $peruApiService): void
    {
        if (empty($this->numero_documento)) {
            $this->warning('Ingresa un número de documento primero.');
            return;
        }

        $this->buscandoApi = true;

        if ($this->tipo_documento === 'DNI') {
            $data = $peruApiService->consultarDni($this->numero_documento);
            if ($data && isset($data['nombres'])) {
                $this->nombres = mb_convert_case($data['nombres'] ?? '', MB_CASE_TITLE, 'UTF-8');
                $this->apellidos = mb_convert_case(
                    trim(($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                    MB_CASE_TITLE,
                    'UTF-8'
                );
                $this->success('Datos obtenidos de RENIEC.');
            } else {
                $this->error('DNI no encontrado o error de API.');
            }
        } elseif ($this->tipo_documento === 'RUC') {
            $data = $peruApiService->consultarRuc($this->numero_documento);
            if ($data && isset($data['razon_social'])) {
                $this->nombres = mb_convert_case($data['razon_social'] ?? '', MB_CASE_TITLE, 'UTF-8');
                $this->apellidos = ''; // RUC no usa apellidos
                $this->direccion = $data['direccion'] ?? '';
                
                // Si la API devuelve un ubigeo como código o array con código
                if (!empty($data['ubigeo'])) {
                    if (is_array($data['ubigeo'])) {
                        // A veces es [dep, prov, dist, code] o similar
                        $this->codigo_ubigeo = (string) end($data['ubigeo']);
                    } else {
                        $this->codigo_ubigeo = (string) $data['ubigeo'];
                    }
                    $this->dispatch('preseleccionarUbigeo', codigo_ubigeo: $this->codigo_ubigeo);
                }
                $this->success('Datos obtenidos de SUNAT.');
            } else {
                $this->error('RUC no encontrado o es inválido.');
            }
        } else {
            $this->info('La búsqueda automática solo aplica para DNI y RUC.');
        }

        $this->buscandoApi = false;
    }

    public function save(): void
    {
        $this->validate([
            'tipo_documento' => 'required|string',
            'numero_documento' => 'required|string|max:15',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|string|max:20',
        ]);

        $clinica_id = Auth::user()->clinica_id;

        // Validar unicidad manual para scope de clínica
        $existe = Cliente::where('clinica_id', $clinica_id)
            ->where('numero_documento', $this->numero_documento)
            ->when($this->cliente_id, fn($query) => $query->where('id', '!=', $this->cliente_id))
            ->exists();

        if ($existe) {
            $this->addError('numero_documento', 'Este documento ya está registrado en tu clínica.');
            return;
        }

        // Determinar codigo_ubigeo basado en distrito_id si no vino de la API
        if ($this->distrito_id) {
            $distrito = UbigeoDistrito::find($this->distrito_id);
            if ($distrito) {
                $this->codigo_ubigeo = $distrito->codigo_ubigeo;
            }
        }

        $data = [
            'clinica_id' => $clinica_id,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'distrito_id' => $this->distrito_id,
            'codigo_ubigeo' => $this->codigo_ubigeo,
            'notas' => $this->notas,
            'activo' => $this->activo,
        ];

        if ($this->isEditing && $this->cliente_id) {
            Cliente::findOrFail($this->cliente_id)->update($data);
            $this->success('Cliente actualizado correctamente.');
        } else {
            Cliente::create($data);
            $this->success('Cliente registrado correctamente.');
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $cliente = Cliente::where('clinica_id', Auth::user()->clinica_id)->findOrFail($id);
        $cliente->delete();
        $this->warning('Cliente eliminado correctamente.');
    }

    private function resetForm(): void
    {
        $this->reset([
            'cliente_id', 'numero_documento', 'nombres', 'apellidos',
            'email', 'telefono', 'direccion', 'distrito_id', 'codigo_ubigeo', 'notas'
        ]);
        $this->tipo_documento = 'DNI';
        $this->activo = true;
        // Forzar limpieza del ubigeo selector
        $this->dispatch('ubigeo-updated', null);
    }

    // Configuración de la tabla Mary UI
    public function headers(): array
    {
        return [
            ['key' => 'documento', 'label' => 'Documento', 'class' => 'w-32'],
            ['key' => 'nombres', 'label' => 'Cliente'],
            ['key' => 'contacto', 'label' => 'Contacto'],
            ['key' => 'activo', 'label' => 'Estado', 'class' => 'w-24'],
        ];
    }

    public function getClientesProperty(): LengthAwarePaginator
    {
        return Cliente::query()
            ->with(['distrito'])
            ->where('clinica_id', Auth::user()->clinica_id)
            ->when($this->search, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('nombres', 'like', "%{$this->search}%")
                      ->orWhere('apellidos', 'like', "%{$this->search}%")
                      ->orWhere('numero_documento', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.clientes.index', [
            'clientes' => $this->clientes,
            'headers' => $this->headers(),
        ]);
    }
}


