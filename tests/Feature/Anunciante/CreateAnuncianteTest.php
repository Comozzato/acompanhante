<?php

declare(strict_types=1);

namespace Tests\Feature\Anunciante;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAnuncianteTest extends TestCase
{
    use RefreshDatabase;
    // Código de teste para criação de Anunciante

    /** @test */
    public function testCreateAnunciante()
    {
        $response = $this->post('/api/auth/register', [
            'cpf' => '04327788295',
            'email' => 'anunciante@teste.com',
            'password' => 'Senha123@',
            'password_confirmation' => 'Senha123@',

        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'anunciante@teste.com']);
    }


    public function testCreateAnuncianteWithInvalidEmail()
    {
        $response = $this->post('/api/auth/register', [
            'cpf' => '04327788295',
            'email' => 'invalid-email',
            'password' => 'Senha123@',
            'password_confirmation' => 'Senha123@',
        ]);
        $response->assertStatus(400);
        // Verifica se a resposta contém o erro de email inválido
        $response->assertJsonFragment([
            'message' => 'O e-mail não é válido.',
        ]);
    }

    public function testCreateAnuncianteWithShortPassword()
    {
        $response = $this->post('/api/auth/register', [
            'cpf' => '04327788295',
            'email' => 'anunciante@teste.com',
            'password' => 'Se123',
            'password_confirmation' => 'Senha123',
        ]);
        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'A senha deve ter pelo menos 6 caracteres.'
        ]);
    }

    public function testCreateAnuncianteWithWeakPassword()
    {
        $response = $this->post('/api/auth/register', [
            'cpf' => '04327788295',
            'email' => 'anunciante@teste.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);
        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'A senha deve conter letras maiúsculas, minúsculas, números e um caractere especial.'
        ]);
    }

    public function testCreateAnuncianteWithInvalidCpf()
    {
        $response = $this->post('/api/auth/register', [
            'cpf' => '12345678901',
            'email' => 'anunciante@teste.com',
            'password' => 'Senha123@',
            'password_confirmation' => 'Senha123@',
        ]);
        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Formato do CPF inválido.',
        ]);
    }

    public function testCreateAnuncianteWithMismatchedPasswords()
    {
        $response = $this->post('/api/auth/register', [
            'cpf' => '04327788295',
            'email' => 'anunciante@teste.com',
            'password' => 'Senha123@',
            'password_confirmation' => 'Senha123',
        ]);
        $response->assertStatus(400);
        // Verifica se a resposta contém o erro de senhas não coincidentes

        $response->assertJsonFragment([
            'message' => 'As senhas não coincidem.',
        ]);
    }

    public function testLoginWithValidCredentials()
    {
        $data = User::factory()->create([
            'password' => bcrypt('Senha123@')
        ]);
        $response = $this->post('/api/auth/login', [
            'email' => $data->email,
            'password' => 'Senha123@',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'refresh_token',
        ]);
    }


    public function testLoginWithInvalidCredentials()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'invalido@teste.com',
            'password' => 'SenhaIncorreta',
        ]);
        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Credenciais inválidas',
        ]);
    }

    public function testWithGetAnunciosCpf()
    {
        $token = $this->fakeLogin();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/anuciante/meus-anuncios');
        $response->assertStatus(200);
    }

    public function testWithGetAnuncioForCpf()
    {
        $token = $this->fakeLogin();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->post('/api/anuciante/buscar-anuncios', [
                'cpf' => '07182340305'
            ]);
        $response->assertStatus(200);
    }

    public function testWithGetAnuncioDados()
    {
        $token = $this->fakeLogin();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/anuciante/dados/85527');
        
        $response->assertStatus(200);
    }

    public function testWithPostAnuncioDados()
    {
        $token = $this->fakeLogin();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->post('/api/anuciante/post/85527', [
                'whatsapp_acompanhante' => '(teste) teste',
            ]);
        $response->assertStatus(200);
    }

    private function fakeLogin($role = 'admn'): string
    {
        $data = User::factory()->create([
            'password' => bcrypt('Senha123@'),
            'role' => $role
        ]);

        $login = $this->post('/api/auth/login', [
            'email' => $data->email,
            'password' => 'Senha123@',
        ]);

        $token = $login->json('access_token');

        return $token;
    }
}
