<?php

namespace App\Listeners;

use App\Events\CheckoutEvent;
use App\Events\CobrancaEvent;
use App\Models\Checkout;

#[Listener]

class CreateCheckout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CheckoutEvent $event): void
    {
        //
        $payload = $event->data;
        info('CreateCheckout Listener triggered with data: ' . json_encode($payload));
        // cria o checkout no banco de dados
        // o payload[0] são os dados da cobranca gerada pelo metodo de pagamento
        // o payload[1] são os dados de produto, post e customer passados para gerar a cobranca
        //dd($payload);
        $checkout = [
            'id' => $payload[0]['id'],
            'post_id' => $payload[1]['post_id'],
            'produto_id' => $payload[1]['produto_id'],
            'customer_id' => $payload[0]['customerId'],
            'expires_at' => $payload[0]['dueDate'],
            'amount' => $payload[0]['value'],
            'status' => $payload[0]['status'],
        ];

        $checkout = Checkout::create($checkout);
        if (! $checkout) {
            info('Failed to create checkout with data: ' . json_encode($checkout));
            return;
        }
        info('Checkout created successfully: ' . json_encode($checkout));
    }
}
