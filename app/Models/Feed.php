<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Feed extends Model
{   
    use Notifiable;
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
    protected $appends = ['notifications'];

    protected $casts = [
        'publicado_em' => 'datetime:d/m/Y H:i:s',
        'ativo' => 'boolean',
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
        return $this->hasMany(Midia::class)->select('id', 'feed_id', 'midia'); // imagens, vídeos etc.
    }

    public static function recommendedForUser(User $user)
    {
        // Aqui entraria o algoritmo futuro
        return self::query(); // por enquanto retorna tudo
    }

    protected static function booted()
    {
        static::deleting(function ($feed) {
            $feed->midia->each->delete();
        });
    }

    public function getNotificationsAttribute()
    {
        return $this->anunciante
            ->notifications
            ->where('data.post_id', $this->id)
            ->sortByDesc('created_at')
            ->values(); // resetar os índices
    }
}
