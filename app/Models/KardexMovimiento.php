<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Movimiento de kardex.
 * Registra toda entrada y salida de stock con trazabilidad completa.
 * Nunca se elimina ni se edita — es un log inmutable.
 */
class KardexMovimiento extends Model
{
    protected $table = 'kardex_movimientos';

    protected $fillable = [
        'clinica_id',
        'producto_id',
        'usuario_id',
        'tipo',
        'cantidad',
        'costo_unitario',
        'lote',
        'fecha_vencimiento',
        'documento_referencia',
        'stock_anterior',
        'stock_posterior',
        'referencia_tipo',
        'referencia_id',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'costo_unitario' => 'decimal:2',
            'fecha_vencimiento' => 'date',
            'stock_anterior' => 'integer',
            'stock_posterior' => 'integer',
        ];
    }

    /**
     * Devuelve el tipo formateado y con color para la UI.
     */
    public function getTipoBadgeAttribute(): array
    {
        return match ($this->tipo) {
            'ENTRADA_COMPRA' => ['label' => 'Compra', 'class' => 'badge-success'],
            'ENTRADA_AJUSTE' => ['label' => 'Ajuste (+)', 'class' => 'badge-info'],
            'SALIDA_VENTA' => ['label' => 'Venta', 'class' => 'badge-warning'],
            'SALIDA_DISPENSACION' => ['label' => 'Dispensación', 'class' => 'badge-primary'],
            'SALIDA_AJUSTE' => ['label' => 'Ajuste (-)', 'class' => 'badge-error'],
            default => ['label' => 'Otro', 'class' => 'badge-ghost'],
        };
    }

    // ── Relaciones ──

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
