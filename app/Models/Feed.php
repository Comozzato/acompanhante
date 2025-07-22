<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    //

    protected $table = 'feed';
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
        return $this->belongsTo(User::class)->select('id');
    }

    public function midia()
    {
        return $this->hasMany(Midia::class)->select('id','feed_id', 'midia'); // imagens, vídeos etc.
    }

    public static function recommendedForUser(User $user)
    {
        // Aqui entraria o algoritmo futuro
        return self::query(); // por enquanto retorna tudo
    }
}
