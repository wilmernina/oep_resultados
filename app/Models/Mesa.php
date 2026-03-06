<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $primaryKey = 'codigo_mesa';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'codigo_mesa',
        'codigo_recinto',
        'numero_mesa',
        'total_votos_validos',
        'votos_blancos',
        'votos_nulos',
        'total_votos_emitidos',
        'acta_imagen',
    ];

    public function recinto(): BelongsTo
    {
        return $this->belongsTo(Recinto::class, 'codigo_recinto', 'codigo_recinto');
    }

    public function organizaciones(): BelongsToMany
    {
        return $this->belongsToMany(OrgPolitica::class, 'mesa_org_politica', 'codigo_mesa', 'codigo_organizacion')
            ->withPivot(['registro_votos']);
    }

    public function detalleVotos(): HasMany
    {
        return $this->hasMany(MesaOrgPolitica::class, 'codigo_mesa', 'codigo_mesa');
    }
}
