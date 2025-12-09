<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    //
    protected $table = 'checkouts';

    protected $id = 'string';

    protected $fillable = [
        'id',
        'post_id',
        'produto_id',
        'customer_id',
        'expires_at',
        'amount',
        'status'
    ];


}
