<?php

declare(strict_types=1);

namespace Tests\Feature\Anunciante;

use App\Models\Session;
use App\Models\User;
use App\Modules\Auth\Session\TokenVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;

class VerifyTokenTest extends \Tests\TestCase
{
    use RefreshDatabase;

    public function testMiddlewareAllowsAuthenticatedUser()
    {

        $user = User::factory()->create([
            'password' => bcrypt('Senha123@')
        ]);
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Senha123@',
        ]);

        $loginResponse->assertStatus(200);

        $token = $loginResponse->json('access_token'); // Ajuste conforme o nome do campo no seu retorno

        // Agora faz a requisição protegida usando o token no header Authorization
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/rota-protegida'); // substitua pela rota que usa seu middleware

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'API is working',
        ]);
    }

    public function testMiddlewareBlocksUnauthenticatedUser()
    {
        $response = $this->getJson('/api/rota-protegida'); // substitua pela rota que usa seu middleware
        $response->assertStatus(401); // ou 403 dependendo de como você configurou o middleware
        $response->assertJsonFragment([
            'message' => 'Não autenticado.'
        ]);
    }

    // public function testMiddlewareBlocksInvalidToken()
    // {
    //     $invalidToken = 'token_invalido';
    //     $response = $this->withHeaders([
    //         'Authorization' => "Bearer $invalidToken"
    //     ])->getJson('/api/rota-protegida'); // substitua pela rota que usa seu middleware

    //     $response->assertStatus(401); // ou 403 dependendo de como você configurou o middleware
    //     $response->assertJsonFragment([
    //         'message' => 'Não autenticado.'
    //     ]);
    // }

    public function testThrowsExceptionIfEmptyKey()
    {
        config(['services.token.key' => '']); // chave vazia

        $verifier = new \App\Modules\Auth\Session\TokenVerifier();

        try {
            $verifier->verify('qualquer-token');
            $this->fail('HttpResponseException não foi lançada');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();
            $data = json_decode($response->getContent(), true); // Decodifica o JSON para um array associativo

            $this->assertEquals(500, $response->getStatusCode());
            $this->assertEquals('Chave de assinatura não configurada.', $data['message']);
        }
    }

    public function testThrowsExceptionIfEmptyAccessToken()
    {
        // Configura a chave para um valor válido para evitar erro anterior
        config(['services.token.key' => base64_encode(config('services.token.key'))]);

        $verifier = new \App\Modules\Auth\Session\TokenVerifier();

        try {
            $verifier->verify('');
            $this->fail('HttpResponseException não foi lançada');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();
            $data = json_decode($response->getContent(), true);

            $this->assertEquals(401, $response->getStatusCode());
            $this->assertEquals('Não autenticado. Sessão não encontrada.', $data['message']);
        }
    }

    public function testThrowsExceptionIfSignatureIsInvalid()
    {
        config(['services.token.key' => base64_encode(config('services.token.key'))]);
        $verifier = new \App\Modules\Auth\Session\TokenVerifier();

        // Monta um token JWT falso com assinatura inválida
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => config('app.url'),
            'exp' => now()->addMinutes(10)->timestamp,
            'sub' => 1,
            'ip' => '127.0.0.1',
            'user_agent' => 'TestAgent',
        ]));
        $signature = 'invalidsignature';

        $invalidToken = $header . '.' . $payload . '.' . $signature;

        try {
            $verifier->verify($invalidToken);
            $this->fail('HttpResponseException não foi lançada para assinatura inválida');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();
            $data = json_decode($response->getContent(), true);
            $this->assertEquals(401, $response->getStatusCode());
            $this->assertEquals('invalido', $data['message']);
        }
    }

    public function testThrowsExceptionIfTokenDoesNotHaveThreeParts()
    {
        config(['services.token.key' => base64_encode(config('services.token.key'))]);

        $verifier = new \App\Modules\Auth\Session\TokenVerifier();

        $invalidToken = 'part1.part2'; // só 2 partes

        try {
            $verifier->verify($invalidToken);
            $this->fail('HttpResponseException não foi lançada para token com partes incorretas');
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $response = $e->getResponse();
            $data = json_decode($response->getContent(), true);
            $this->assertEquals(401, $response->getStatusCode());
            $this->assertEquals('Token inválido.', $data['message']);
        }
    }
}
