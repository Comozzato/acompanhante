<?php

namespace App\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AsaasEvent  implements ShouldQueue
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
        info('AsaasEvent fired: ' . $this->event . ' Payload: ' . json_encode($this->payload['payment']));
    }
}
