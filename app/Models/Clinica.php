<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de clínica veterinaria.
 * Entidad raíz del multi-tenant lógico.
 */
class Clinica extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'ruc',
        'razon_social',
        'direccion',
        'telefono',
        'email',
        'logo',
        'sitio_web',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    // ── Relaciones ──

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
