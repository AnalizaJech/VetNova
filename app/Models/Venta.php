<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinica_id',
        'cliente_id',
        'cajero_id',
        'subtotal',
        'igv',
        'total',
        'metodo_pago',
        'estado',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
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

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }
}
