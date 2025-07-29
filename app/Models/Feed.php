<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    //

    protected $table = 'feed';
    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'conteudo',
        'ativo',
        'publicado_em',
    ];

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
