<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinica_id',
        'tipo',
        'categoria',
        'nombre',
        'codigo_barras',
        'precio_venta',
        'costo_compra',
        'afecto_igv',
        'stock_actual',
        'stock_minimo',
        'activo',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'precio_venta' => 'decimal:2',
            'costo_compra' => 'decimal:2',
            'afecto_igv' => 'boolean',
            'activo' => 'boolean',
            'stock_actual' => 'integer',
            'stock_minimo' => 'integer',
        ];
    }

    /**
     * Helper para saber si tiene stock bajo.
     * Siempre devuelve false si es un servicio.
     */
    public function getStockBajoAttribute(): bool
    {
        if ($this->tipo === 'SERVICIO') {
            return false;
        }
        return $this->stock_actual <= $this->stock_minimo;
    }

    // ── Relaciones ──

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }
}
