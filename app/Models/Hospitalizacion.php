<?php

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
        'estado'
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_alta' => 'datetime',
    ];

    public function mascota(): BelongsTo
    {
        return $this->belongsTo(Mascota::class);
    }

    public function notas(): HasMany
    {
        return $this->hasMany(HospitalizacionNota::class)->orderBy('created_at', 'desc');
    }
}
