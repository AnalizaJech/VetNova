<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Distrito del Perú (tercer nivel de ubigeo).
 * codigo_ubigeo es el código de 6 dígitos usado por SUNAT/PeruAPI.
 */
class UbigeoDistrito extends Model
{
    public $timestamps = false;

    protected $table = 'ubigeo_distritos';

    protected $fillable = ['provincia_id', 'nombre', 'codigo', 'codigo_ubigeo'];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(UbigeoProvincia::class, 'provincia_id');
    }
}
