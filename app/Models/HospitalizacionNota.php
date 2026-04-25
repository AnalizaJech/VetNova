<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalizacionNota extends Model
{
    protected $table = 'hospitalizacion_notas';

    protected $fillable = [
        'hospitalizacion_id',
        'veterinario_id',
        'nota'
    ];

    public function veterinario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'veterinario_id');
    }

    public function hospitalizacion(): BelongsTo
    {
        return $this->belongsTo(Hospitalizacion::class);
    }
}
