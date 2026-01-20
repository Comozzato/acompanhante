<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Posts extends Model implements Auditable
{
    //
    use AuditableTrait;

    protected $table = 'posts';

    protected $fillable = [
        'id',
        'user_id',
        'imgcapa',
        'imgevidencias',
        'imgatualizadas',
        'status',
        'nome',
        'cidade',
        'url',
        'cidade_virtual',
        'cidades_virtuais',
    ];



    public $timestamps = false;

    protected $hidden = [
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feeds()
    {
        return $this->hasMany(Feed::class, 'post_id');
    }


    public function feedslimits3()
    {
        return $this->hasMany(Feed::class, 'post_id')
            ->aprovado()
            ->story()
            ->orderByDesc('publicado_em')
            ->limit(3); // pega os 3 últimos feeds de cada post
    }

    public function scopeCity($query, $city)
    {
        if (!$city) {
            return $query;
        }

        return $query->where(function ($q) use ($city) {

            // Caso 1: cidade normal
            $q->where('cidade', $city)

                // Caso 2: cidade virtual
                ->orWhere(function ($q2) use ($city) {
                    $q2->whereNull('cidade')
                        ->where('cidade_virtual', true)
                        ->whereJsonContains('cidades_virtuais', $city);
                });
        });
    }

    public function scopePublish($query)
    {
        return $query->where('status', 'Publicado');
    }
}
