<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\AsaasEvent;
use Illuminate\Http\Request;

class AsaasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Salvar log opcional
        info('Webhook Asaas recebido:', $request->all());
        AsaasEvent::dispatch($request->all());
        return response()->json(['status' => 'ok']);
    }
}
