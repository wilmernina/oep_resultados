<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgPolitica extends Model
{
    protected $table = 'org_politicas';
    protected $primaryKey = 'codigo_organizacion';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_organizacion',
        'nombre_organizacion',
        'sigla',
        'color_hex',
    ];

    public function mesas(): BelongsToMany
    {
        return $this->belongsToMany(Mesa::class, 'mesa_org_politica', 'codigo_organizacion', 'codigo_mesa')
            ->withPivot(['registro_votos']);
    }

    public function detalleVotos(): HasMany
    {
        return $this->hasMany(MesaOrgPolitica::class, 'codigo_organizacion', 'codigo_organizacion');
    }
}
