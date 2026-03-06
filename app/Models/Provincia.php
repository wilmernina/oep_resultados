<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provincia extends Model
{
    protected $primaryKey = 'codigo_provincia';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_provincia',
        'nombre_provincia',
        'codigo_departamento',
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'codigo_departamento', 'codigo_departamento');
    }

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'codigo_provincia', 'codigo_provincia');
    }
}

