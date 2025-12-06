<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AsaasEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Dados brutos enviados pelo Asaas (webhook).
     *
     * @var array
     */
    public array $payload;

    /**
     * Tipo de evento principal, ex: CHECKOUT_PAID, CHECKOUT_CREATED etc.
     *
     * @var string|null
     */
    public ?string $event;

    /**
     * Create a new event instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->event = $payload['event'] ?? null;
    }
}
