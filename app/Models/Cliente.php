<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinica_id',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellidos',
        'email',
        'telefono',
        'direccion',
        'codigo_ubigeo',
        'distrito_id',
        'activo',
        'notas',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Helper para obtener el nombre completo.
     * Si es empresa (RUC sin apellidos), solo devuelve los nombres.
     */
    public function getNombreCompletoAttribute(): string
    {
        if (empty($this->apellidos)) {
            return $this->nombres;
        }

        return "{$this->nombres} {$this->apellidos}";
    }

    // ── Relaciones ──

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(UbigeoDistrito::class, 'distrito_id');
    }

    public function mascotas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Mascota::class);
    }
}
