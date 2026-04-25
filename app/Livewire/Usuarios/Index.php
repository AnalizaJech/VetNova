<?php

declare(strict_types=1);

namespace App\Livewire\Usuarios;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use App\Traits\AlertModal;

/**
 * Módulo de gestión de usuarios del sistema.
 * Solo accesible para usuarios con permiso 'usuarios.ver'.
 * Permite crear, editar, desactivar y asignar roles a usuarios de la clínica.
 */
#[Layout('components.layouts.app')]
#[Title('Gestión de Usuarios — VetNova')]
class Index extends Component
{
    use WithPagination, AlertModal;

    // Búsqueda
    public string $search = '';

    // Modal
    public bool $modalModal = false;
    public bool $isEditing = false;

    // Formulario
    public ?int $user_id = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $telefono = '';
    public string $dni = '';
    public ?string $rol = null;
    public bool $activo = true;

    // Roles disponibles para asignar
    public array $rolesDisponibles = [];

    public function mount(): void
    {
        $this->cargarRoles();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Carga los roles disponibles excluyendo super_admin
     * (solo un super_admin puede asignar ese rol).
     */
    private function cargarRoles(): void
    {
        $query = Role::query();

        // Solo super_admin puede ver/asignar el rol super_admin
        if (!auth()->user()->hasRole('super_admin')) {
            $query->where('name', '!=', 'super_admin');
        }

        $this->rolesDisponibles = $query->get()
            ->map(fn ($role) => ['id' => $role->name, 'name' => $this->formatearNombreRol($role->name)])
            ->toArray();
    }

    /**
     * Formatea el nombre del rol para la UI (snake_case → Title Case).
     */
    private function formatearNombreRol(string $rol): string
    {
        return ucwords(str_replace('_', ' ', $rol));
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->modalModal = true;
    }

    public function edit(User $user): void
    {
        // Verificar que el usuario pertenece a la misma clínica
        if ($user->clinica_id !== auth()->user()->clinica_id) {
            $this->error('No puedes editar usuarios de otra clínica.');
            return;
        }

        $this->resetForm();
        $this->isEditing = true;

        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->telefono = $user->telefono ?? '';
        $this->dni = $user->dni ?? '';
        $this->activo = $user->activo;
        $this->rol = $user->roles->first()?->name;

        $this->modalModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telefono' => 'nullable|string|max:20',
            'dni' => 'nullable|string|max:15',
            'rol' => 'required|string',
        ];

        // Contraseña obligatoria solo al crear
        if (!$this->isEditing) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $this->validate($rules, [
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'rol.required' => 'Debes asignar un rol al usuario.',
        ]);

        $clinica_id = auth()->user()->clinica_id;

        // Validar email único dentro de la clínica
        $existe = User::where('clinica_id', $clinica_id)
            ->where('email', $this->email)
            ->when($this->user_id, fn ($q) => $q->where('id', '!=', $this->user_id))
            ->exists();

        if ($existe) {
            $this->addError('email', 'Este email ya está registrado en tu clínica.');
            return;
        }

        $data = [
            'clinica_id' => $clinica_id,
            'sucursal_id' => auth()->user()->sucursal_id, // Misma sucursal por defecto
            'name' => $this->name,
            'email' => $this->email,
            'telefono' => $this->telefono ?: null,
            'dni' => $this->dni ?: null,
            'activo' => $this->activo,
        ];

        if ($this->isEditing && $this->user_id) {
            $user = User::where('clinica_id', $clinica_id)->findOrFail($this->user_id);

            // Si se proporcionó nueva contraseña, actualizarla
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);

            // Sincronizar rol (remueve el anterior y asigna el nuevo)
            $user->syncRoles([$this->rol]);

            $this->success('Usuario actualizado correctamente.');
        } else {
            $data['password'] = Hash::make($this->password);
            $user = User::create($data);
            $user->assignRole($this->rol);
            $this->success('Usuario creado correctamente.');
        }

        $this->modalModal = false;
        $this->resetForm();
    }

    /**
     * Desactivar/activar un usuario (soft toggle, no eliminación real).
     */
    public function toggleActivo(int $id): void
    {
        $user = User::where('clinica_id', auth()->user()->clinica_id)->findOrFail($id);

        // No permitir desactivarse a sí mismo
        if ($user->id === auth()->id()) {
            $this->error('No puedes desactivar tu propia cuenta.');
            return;
        }

        $user->update(['activo' => !$user->activo]);

        $estado = $user->activo ? 'activado' : 'desactivado';
        $this->success("Usuario {$estado} correctamente.");
    }

    private function resetForm(): void
    {
        $this->reset([
            'user_id', 'name', 'email', 'password', 'password_confirmation',
            'telefono', 'dni', 'rol'
        ]);
        $this->activo = true;
    }

    // Configuración de tabla Mary UI
    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Nombre'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'rol', 'label' => 'Rol', 'class' => 'hidden md:table-cell'],
            ['key' => 'activo', 'label' => 'Estado', 'class' => 'w-24'],
        ];
    }

    public function getUsersProperty(): LengthAwarePaginator
    {
        return User::with('roles')
            ->where('clinica_id', auth()->user()->clinica_id)
            ->when($this->search, function (Builder $query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('dni', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.usuarios.index', [
            'users' => $this->users,
            'headers' => $this->headers(),
        ]);
    }
}
