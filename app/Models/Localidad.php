<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Localidad extends Model
{
    protected $table = 'localidades';
    protected $primaryKey = 'codigo_localidad';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_localidad',
        'nombre_localidad',
        'codigo_municipio',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'codigo_municipio', 'codigo_municipio');
    }

    public function recintos(): HasMany
    {
        return $this->hasMany(Recinto::class, 'codigo_localidad', 'codigo_localidad');
    }
}
