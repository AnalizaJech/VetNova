<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroPreventivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registros_preventivos';

    protected $fillable = [
        'clinica_id',
        'mascota_id',
        'veterinario_id',
        'tipo',
        'producto_o_enfermedad',
        'lote_marca',
        'peso_al_momento',
        'fecha_aplicacion',
        'fecha_proxima',
        'notas',
        'notificado_sms',
        'notificado_whatsapp',
        'notificado_email',
    ];

    protected function casts(): array
    {
        return [
            'fecha_aplicacion' => 'date',
            'fecha_proxima' => 'date',
            'peso_al_momento' => 'decimal:2',
            'notificado_sms' => 'boolean',
            'notificado_whatsapp' => 'boolean',
            'notificado_email' => 'boolean',
        ];
    }

    /**
     * Devuelve el tipo de forma amigable y con color para la UI.
     */
    public function getTipoBadgeAttribute(): array
    {
        return match ($this->tipo) {
            'VACUNA' => ['label' => 'Vacuna', 'class' => 'badge-success'],
            'DESPARASITACION_INT' => ['label' => 'Desparasitación Interna', 'class' => 'badge-info'],
            'DESPARASITACION_EXT' => ['label' => 'Desparasitación Externa', 'class' => 'badge-warning'],
            default => ['label' => 'Otro', 'class' => 'badge-ghost'],
        };
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
}
