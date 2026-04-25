<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mascota extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinica_id',
        'cliente_id',
        'nombre',
        'especie',
        'raza',
        'sexo',
        'color',
        'fecha_nacimiento',
        'peso_actual',
        'foto',
        'esterilizado',
        'fallecido',
        'notas_medicas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'peso_actual' => 'decimal:2',
            'esterilizado' => 'boolean',
            'fallecido' => 'boolean',
        ];
    }

    /**
     * Helper para calcular la edad de la mascota de forma legible.
     */
    public function getEdadReadableAttribute(): string
    {
        if (!$this->fecha_nacimiento) {
            return 'Desconocida';
        }

        $now = Carbon::now();
        $diff = $this->fecha_nacimiento->diff($now);

        if ($diff->y > 0) {
            return "{$diff->y} años" . ($diff->m > 0 ? " {$diff->m} meses" : '');
        }

        if ($diff->m > 0) {
            return "{$diff->m} meses";
        }

        return "{$diff->d} días";
    }

    // ── Relaciones ──

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
