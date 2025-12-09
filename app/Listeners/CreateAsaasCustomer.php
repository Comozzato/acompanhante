<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Helpers\Asaas;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

#[Listener]

class CreateAsaasCustomer implements ShouldQueue
{     
    public function handle(UserRegistered $event)
    {   
        info('CreateAsaasCustomer Listener triggered');
        // Lógica para criar o cliente no Asaas
        $user = $event->user;

        if ($user->asaas_customer_id) {
        return;
        }

         $data = [
            'name' => 'musa: ' .  Str::before($user->email, '@'),
            'email' => $user->email,
            //'phone' => $userData->phone,
            //'mobilePhone' => $userData->phone,
            'cpfCnpj' => $user->cpf,
            'externalReference' => $user->id,
            'notificationDisabled' => true,
        ];
         try {
            $response = Asaas::init()->post('/v3/customers', $data);
            $response = json_decode($response, true);
            if (! isset($response['id'])) {
                throw new \Exception('Resposta inválida do Asaas');
            }

            $user->update([
                'asaas_customer_id' => $response['id'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao criar cliente no Asaas', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // deixa a queue fazer retry
        }
        return;
    }

}
