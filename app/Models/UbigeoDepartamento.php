<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Departamento del Perú (primer nivel de ubigeo).
 * Datos sembrados desde eApi Perú al instalar.
 */
class UbigeoDepartamento extends Model
{
    public $timestamps = false;

    protected $table = 'ubigeo_departamentos';

    protected $fillable = ['nombre', 'codigo'];

    public function provincias(): HasMany
    {
        return $this->hasMany(UbigeoProvincia::class, 'departamento_id');
    }
}
