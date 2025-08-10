<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    //

    protected $table = 'feed';
    protected $fillable = [
        'user_id',
        'post', // Renomeado de conteudo para post
        'post_id', // Novo campo adicionado
        'ativo',
        'publicado_em',
        'publish', // Novo campo adicionado para controle de publicação
    ];

    protected $casts = [
        'publicado_em' => 'datetime:d/m/Y H:i:s',
        'ativo' => 'boolean',
        'publish' => 'boolean', // Novo campo adicionado para controle de publicação
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function anunciante()
    {
        return $this->belongsTo(User::class, 'user_id')->select('id'); // Anunciante que criou o post
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
