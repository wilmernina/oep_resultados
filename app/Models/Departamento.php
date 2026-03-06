<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    protected $primaryKey = 'codigo_departamento';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_departamento',
        'nombre_departamento',
    ];

    public function provincias(): HasMany
    {
        return $this->hasMany(Provincia::class, 'codigo_departamento', 'codigo_departamento');
    }
}

