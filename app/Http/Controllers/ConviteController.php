<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Behaviors\EmailBehaviors;
use App\Mail\ConviteMail;
use App\Services\AnuncioApiService;

use Illuminate\Http\Request;
use Mail;


class ConviteController extends Controller
{
    public function __construct(private AnuncioApiService $service)
    {
        //
    }

     public function enviarConvite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $email = new EmailBehaviors($validated['email']);
            Mail::to(  $email->getValue())->send(new ConviteMail());
            return response()->json(['message' => 'Convite enviado com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao enviar o convite.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}