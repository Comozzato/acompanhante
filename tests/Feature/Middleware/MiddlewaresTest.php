<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\User;

class MiddlewaresTest extends \Tests\TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function it_can_access_protected_route_with_valid_token()
    {
        $data = User::factory()->create([
            'password' => bcrypt('Senha123@')
        ]);
        $response = $this->post('/api/auth/login', [
            'email' => $data->email,
            'password' => 'Senha123@',
        ]);
        $response->assertStatus(200);
        $token = $response->json('access_token');
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('/api/rota-protegida-jwt');

        $response->assertStatus(200);
    }
    /** @test */
    public function it_cannot_access_protected_route_without_token()
    {
        $response = $this->get('/api/rota-protegida-jwt');
        $response->assertStatus(401);
    }
    /** @test */
    public function it_cannot_access_protected_route_with_invalid_token()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid_token'])
            ->get('/api/rota-protegida-jwt');
        $response->assertStatus(401);
    }
    /** @test */
    public function it_can_access_protected_route_with_valid_basic_auth()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env('basic_auth_username') . ':' . env('basic_auth_password'))
        ])->get('/api/rota-protegida-basic');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_cannot_access_protected_route_without_basic_auth()
    {
        $response = $this->get('/api/rota-protegida-basic');
        $response->assertStatus(401);
    }
    /** @test */
    public function it_cannot_access_protected_route_with_invalid_basic_auth()
    {
        $response = $this->withHeaders(['Authorization' => 'Basic ' . base64_encode('invalid_user:invalid_password')])
            ->get('/api/rota-protegida-basic');
        $response->assertStatus(401);
    }
}
