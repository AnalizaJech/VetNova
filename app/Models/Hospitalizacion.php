<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospitalizacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hospitalizaciones';

    protected $fillable = [
        'clinica_id',
        'mascota_id',
        'jaula',
        'motivo_ingreso',
        'fecha_ingreso',
        'fecha_alta',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_alta' => 'datetime',
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

    public function notas(): HasMany
    {
        return $this->hasMany(HospitalizacionNota::class)->orderBy('created_at', 'desc');
    }
}

