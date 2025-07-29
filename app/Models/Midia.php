<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Midia extends Model
{
    //
    protected $table = 'midia';

    protected $fillable = ['feed_id', 'midia'];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->temporaryUrl($this->midia, now()->addMinutes(15));
    }
}
