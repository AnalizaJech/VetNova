<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Prescripción médica vinculada a una historia clínica.
 * Puede referenciar un producto del inventario para dispensación.
 */
class Prescripcion extends Model
{
    use HasFactory;

    protected $table = 'prescripciones';

    protected $fillable = [
        'clinica_id',
        'historia_clinica_id',
        'producto_id',
        'medicamento',
        'dosis',
        'via_administracion',
        'duracion_dias',
        'indicaciones',
        'cantidad_dispensada',
        'dispensado',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_dispensada' => 'integer',
            'duracion_dias' => 'integer',
            'dispensado' => 'boolean',
        ];
    }

    // ── Relaciones ──

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function historiaClinica(): BelongsTo
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
