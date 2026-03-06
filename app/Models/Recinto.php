<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recinto extends Model
{
    protected $primaryKey = 'codigo_recinto';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_recinto',
        'nombre_recinto',
        'codigo_localidad',
    ];

    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'codigo_localidad', 'codigo_localidad');
    }

    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'codigo_recinto', 'codigo_recinto');
    }
}

