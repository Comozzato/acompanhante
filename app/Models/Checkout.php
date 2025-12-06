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
        'customer_id',
        'expires_at',
        'amount',
        'status',
    ];


}
