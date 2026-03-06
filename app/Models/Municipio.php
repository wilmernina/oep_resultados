<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
    protected $primaryKey = 'codigo_municipio';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_municipio',
        'nombre_municipio',
        'codigo_provincia',
    ];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'codigo_provincia', 'codigo_provincia');
    }

    public function localidades(): HasMany
    {
        return $this->hasMany(Localidad::class, 'codigo_municipio', 'codigo_municipio');
    }
}

