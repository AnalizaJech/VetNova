<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Provincia del Perú (segundo nivel de ubigeo).
 */
class UbigeoProvincia extends Model
{
    public $timestamps = false;

    protected $table = 'ubigeo_provincias';

    protected $fillable = ['departamento_id', 'nombre', 'codigo'];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(UbigeoDepartamento::class, 'departamento_id');
    }

    public function distritos(): HasMany
    {
        return $this->hasMany(UbigeoDistrito::class, 'provincia_id');
    }
}
