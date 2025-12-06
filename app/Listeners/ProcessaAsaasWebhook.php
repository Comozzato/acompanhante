<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AsaasEvent;


#[Listener]
class ProcessaAsaasWebhook
{
    public function handle(AsaasEvent $event): void
    {   
        info('Processando evento Asaas: ' . $event->event);
        $payload = $event->payload;
        $tipo = $event->event;
        match ($tipo) {
            'CHECKOUT_CREATED'  => $this->handleCheckoutCreated($payload),
            'CHECKOUT_PAID'     => $this->handleCheckoutPaid($payload),
            'CHECKOUT_CANCELED' => $this->handleCheckoutCanceled($payload),
            'CHECKOUT_EXPIRED'  => $this->handleCheckoutExpired($payload),
            default             => null,
        };
    }
    protected function handleCheckoutPaid(array $payload): void
    {
        info($payload, 'Asaas CHECKOUT_PAID recebido: ');
    }

    protected function handleCheckoutCreated(array $payload): void
    {
        // ...
    }

    protected function handleCheckoutCanceled(array $payload): void
    {
        // ...
    }

    protected function handleCheckoutExpired(array $payload): void
    {
        // ...
    }
}
