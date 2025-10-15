<?php

namespace App\Models;

use Carbon\Carbon;
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
        'tipo', // Novo campo adicionado para diferenciar tipos de posts
        'publicado_em',
        'publish', // Novo campo adicionado para controle de publicação
        'expires_at', // Novo campo adicionado para expiração de stories
        'tipo_arquivo', // Novo campo adicionado para tipo de arquivo
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

    public function scopeTypeMidia($query, $tipo)
    {
        if ($tipo === 'geral') {
            // Traz todos os feeds que tenham qualquer mídia
            $query->whereHas('midia')->with('midia');
        } elseif ($tipo === 'video') {
            // Garante que tem pelo menos um vídeo
            $query->whereHas('midia', fn($q) => $q->ofTipo('video'))
                ->with(['midia' => function ($q) {
                    $q->where(function ($sub) {
                        $sub->ofTipo('video')
                            ->orWhere(function ($qq) {
                                $qq->ofTipo('imagem');
                            });
                    });
                }]);
        } elseif ($tipo === 'imagem') {
            // só imagens que NÃO são thumbnails
            return $query->whereHas('midia', fn($q) => $q->ofTipo('imagem')->where('midia', 'NOT ILIKE', '%thumb%'))
                ->with(['midia' => fn($q) => $q->ofTipo('imagem')->where('midia', 'NOT ILIKE', '%thumb%')]);
        }
    }

    public function post()
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    public function posts_info()
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    public function scopeAprovado($query)
    {
        return $query->where('publish', 'Aprovado');
    }
    public function scopeStory($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('expires_at')
                ->where('expires_at', '>', Carbon::now('America/Sao_Paulo'));
        });
    }

    public function scopePost($query)
    {
        return $query->where('tipo', 'post');
    }
}
