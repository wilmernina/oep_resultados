<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MesaOrgPolitica extends Model
{
    protected $table = 'mesa_org_politica';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    protected $fillable = [
        'codigo_mesa',
        'codigo_organizacion',
        'registro_votos',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'codigo_mesa', 'codigo_mesa');
    }

    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(OrgPolitica::class, 'codigo_organizacion', 'codigo_organizacion');
    }
}
