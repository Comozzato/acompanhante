<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    //
    protected $table = 'posts';

    protected $fillable = [
        'id',
        'user_id',
        'imgcapa',
        'imgevidencias',
        'imgatualizadas',
        'nome',
        'cidade',
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
        return $this->hasMany(Feed::class, 'post_id')->ultimas24Horas();
    }

    public function scopeCity($query, $city)
    {
        if ($city) {
            return $query->where('cidade', $city);
        }
        return $query;
    }
}

