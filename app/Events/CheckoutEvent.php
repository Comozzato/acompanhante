<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutEvent
{
    use Dispatchable, SerializesModels;

    public array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
        info('CheckoutEvent dispatched with data: ' . json_encode($data));
    }
}
