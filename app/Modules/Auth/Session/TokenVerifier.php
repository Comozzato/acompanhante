<?php

declare(strict_types=1);

namespace App\Modules\Auth\Session;

use App\Models\Session;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TokenVerifier
{
    private readonly string $key;

    public function __construct()
    {
        $this->key = base64_decode(config('services.token.key'));
    }
    public function verify(string $accessToken): array
    {
        if (empty($accessToken)) {
            throw new HttpResponseException(response(['message' => 'Não autenticado. Sessão não encontrada.'], Response::HTTP_UNAUTHORIZED));
        }
        $this->verifyAssinatura($accessToken);
        $payload = $this->decodePayload($accessToken);
        if ($payload['iss'] !== config('app.url')) {
            throw new HttpResponseException(response(['message' => 'Não autenticado. Sessão não encontrada.'], Response::HTTP_UNAUTHORIZED));
        }
        if ($payload['exp'] < now()->timestamp) {
            throw new HttpResponseException(response(['message' => 'Token expirado.'], Response::HTTP_UNAUTHORIZED));
        }
        $this->verifySession($accessToken, $payload);
        return $payload;
    }

    private function decodePayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new HttpResponseException(response(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) {
            throw new HttpResponseException(response(['message' => 'Payload inválido.'], Response::HTTP_UNAUTHORIZED));
        }
        return $payload;
    }

    private function verifyAssinatura($jwt)
    {
        list($headerB64, $payloadB64, $signatureB64) = explode('.', $jwt);
        // Recria a assinatura
        $expectedSignature = hash_hmac('sha256', "$headerB64.$payloadB64", $this->key, true);
        $expectedSignatureB64 = rtrim(strtr(base64_encode($expectedSignature), '+/', '-_'), '=');
        // Compara com a assinatura do token
        if (!hash_equals($expectedSignatureB64, $signatureB64)) {
            throw new HttpResponseException(response(['message' => 'invalido'], Response::HTTP_UNAUTHORIZED));
        }
    }

    private function verifySession($accessToken, $payload): void
    {

        $chaveCache = 'session:' . $accessToken . ':' . $payload['sub'];
        $session = Cache::store('file')->get($chaveCache);
        if ($session) {
            return;
        }

        if (
            !Session::where('user_id', $payload['sub'])
                ->where('ip_address', '=', $payload['ip'])
                ->where('user_agent', '=', $payload['user_agent'])
                ->where('access_token', '=', $accessToken)
                ->exists()
        ) {
            throw new HttpResponseException(response(['message' => 'Sessão não encontrada.'], Response::HTTP_UNAUTHORIZED));
        }

        Cache::store('file')->put($chaveCache, true, now()->addMinutes(10));
    }
}