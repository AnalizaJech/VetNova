<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoriaClinica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historias_clinicas';

    protected $fillable = [
        'clinica_id',
        'mascota_id',
        'veterinario_id',
        'cita_id',
        'fecha',
        'motivo_consulta',
        'peso',
        'temperatura',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'anamnesis',
        'diagnostico_presuntivo',
        'tratamiento_indicaciones',
        'proxima_cita_recomendada',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'peso' => 'decimal:2',
            'temperatura' => 'decimal:1',
            'proxima_cita_recomendada' => 'date',
        ];
    }

    // ── Relaciones ──

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class);
    }

    public function veterinario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'veterinario_id');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function prescripciones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Prescripcion::class, 'historia_clinica_id');
    }
}
