<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    //
    protected $fillable = [
        'anunciante_id',
        'tipo',
        'titulo',
        'conteudo',
        'midia_path',
        'ativo',
        'publicado_em',
    ];

    protected $casts = [
        'publicado_em' => 'datetime',
        'ativo' => 'boolean',
    ];

    public function anunciante()
    {
        return $this->belongsTo(User::class)->select('name');
    }

}
