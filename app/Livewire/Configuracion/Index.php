<?php

declare(strict_types=1);

namespace App\Livewire\Configuracion;

use App\Models\Clinica;
use App\Models\Sucursal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Traits\AlertModal;

/**
 * Módulo de configuración de la clínica actual.
 * Permite editar datos generales, sucursales y ajustes del sistema.
 * Solo accesible para administradores y super_admin.
 */
#[Layout('components.layouts.app')]
#[Title('Configuración — VetNova')]
class Index extends Component
{
    use AlertModal;

    // Datos de la clínica
    public ?int $clinica_id = null;
    public string $nombre = '';
    public string $ruc = '';
    public string $razon_social = '';
    public string $direccion = '';
    public string $telefono = '';
    public string $email = '';
    public string $sitio_web = '';

    // Modal sucursal
    public bool $modalSucursal = false;
    public bool $isEditingSucursal = false;
    public ?int $sucursal_id = null;
    public string $sucursal_nombre = '';
    public string $sucursal_direccion = '';
    public string $sucursal_telefono = '';
    public string $sucursal_email = '';
    public bool $sucursal_activo = true;

    public function mount(): void
    {
        $clinica = Clinica::findOrFail(auth()->user()->clinica_id);

        $this->clinica_id = $clinica->id;
        $this->nombre = $clinica->nombre;
        $this->ruc = $clinica->ruc ?? '';
        $this->razon_social = $clinica->razon_social ?? '';
        $this->direccion = $clinica->direccion ?? '';
        $this->telefono = $clinica->telefono ?? '';
        $this->email = $clinica->email ?? '';
        $this->sitio_web = $clinica->sitio_web ?? '';
    }

    /**
     * Guardar datos generales de la clínica.
     */
    public function guardarClinica(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:100',
            'ruc' => 'required|string|size:11',
            'razon_social' => 'required|string|max:150',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'sitio_web' => 'nullable|url|max:150',
        ], [
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'sitio_web.url' => 'Ingresa una URL válida (ej: https://ejemplo.com).',
        ]);

        $clinica = Clinica::findOrFail($this->clinica_id);
        $clinica->update([
            'nombre' => $this->nombre,
            'ruc' => $this->ruc,
            'razon_social' => $this->razon_social,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'sitio_web' => $this->sitio_web,
        ]);

        $this->success('Datos de la clínica actualizados correctamente.');
    }

    // ── CRUD de Sucursales ──

    public function crearSucursal(): void
    {
        $this->resetSucursalForm();
        $this->isEditingSucursal = false;
        $this->modalSucursal = true;
    }

    public function editarSucursal(Sucursal $sucursal): void
    {
        $this->resetSucursalForm();
        $this->isEditingSucursal = true;

        $this->sucursal_id = $sucursal->id;
        $this->sucursal_nombre = $sucursal->nombre;
        $this->sucursal_direccion = $sucursal->direccion ?? '';
        $this->sucursal_telefono = $sucursal->telefono ?? '';
        $this->sucursal_email = $sucursal->email ?? '';
        $this->sucursal_activo = $sucursal->activo;

        $this->modalSucursal = true;
    }

    public function guardarSucursal(): void
    {
        $this->validate([
            'sucursal_nombre' => 'required|string|max:100',
            'sucursal_direccion' => 'nullable|string|max:200',
            'sucursal_telefono' => 'nullable|string|max:20',
            'sucursal_email' => 'nullable|email|max:100',
        ]);

        $data = [
            'clinica_id' => $this->clinica_id,
            'nombre' => $this->sucursal_nombre,
            'direccion' => $this->sucursal_direccion,
            'telefono' => $this->sucursal_telefono,
            'email' => $this->sucursal_email,
            'activo' => $this->sucursal_activo,
        ];

        if ($this->isEditingSucursal && $this->sucursal_id) {
            Sucursal::where('clinica_id', $this->clinica_id)
                ->findOrFail($this->sucursal_id)
                ->update($data);
            $this->success('Sucursal actualizada.');
        } else {
            Sucursal::create($data);
            $this->success('Sucursal creada correctamente.');
        }

        $this->modalSucursal = false;
        $this->resetSucursalForm();
    }

    public function eliminarSucursal(int $id): void
    {
        $sucursal = Sucursal::where('clinica_id', $this->clinica_id)->findOrFail($id);

        // No permitir eliminar la sucursal principal
        if ($sucursal->principal) {
            $this->error('No puedes eliminar la sucursal principal.');
            return;
        }

        $sucursal->delete();
        $this->warning('Sucursal eliminada.');
    }

    private function resetSucursalForm(): void
    {
        $this->reset([
            'sucursal_id', 'sucursal_nombre', 'sucursal_direccion',
            'sucursal_telefono', 'sucursal_email'
        ]);
        $this->sucursal_activo = true;
    }

    public function render()
    {
        $sucursales = Sucursal::where('clinica_id', $this->clinica_id)
            ->orderBy('principal', 'desc')
            ->orderBy('nombre')
            ->get();

        return view('livewire.configuracion.index', compact('sucursales'));
    }
}
