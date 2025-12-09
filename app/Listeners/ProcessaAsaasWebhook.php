<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AsaasEvent;
use App\Models\Checkout;

#[Listener]
class ProcessaAsaasWebhook
{
    public function handle(AsaasEvent $event): void
    {   
        info('Processando evento Asaas: ' . $event->event);
        $payload = $event->payload;
        $tipo = $event->event;
        $payId = $payload['payment']['id'];
        if(!isset($payId)){
            info('Payload inválido: ID de pagamento ausente.');
            return;
        }
        if ($tipo === null) {
            info('Tipo de evento inválido: ' . $tipo);
            return;
        }
        
        match ($tipo) {
            'CHECKOUT_CREATED'  => $this->handleCheckoutCreated($payload),
            'CHECKOUT_PAID'     => $this->handleCheckoutPaid($payload),
            'CHECKOUT_CANCELED' => $this->handleCheckoutCanceled($payload),
            'CHECKOUT_EXPIRED'  => $this->handleCheckoutExpired($payload),
            default             => null,
        };
        $this->updateCheckoutStatus($payload['payment']['id'], $tipo);
    }
    protected function handleCheckoutPaid(array $payload): void
    {
        
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


    protected function updateCheckoutStatus(string $checkoutId, string $status): void
    {
        // ...

        $checkout = Checkout::find($checkoutId);
        if ($checkout) {
            $checkout->status = $status;
            $checkout->save();
            info("Checkout {$checkoutId} status updated to {$status}");
        } else {
            info("Checkout {$checkoutId} not found");   
        return;
    }
}
}
