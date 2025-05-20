<?php
namespace Tests\Feature\Modules\Anunciante;

use Tests\TestCase;

use Mockery;

class AnuncianteControllerTest extends TestCase
{
    public function test_get_dados_retorna_dados_do_anunciante()
    {
        $service = \Mockery::mock(\App\Modules\Anunciante\Services\AnuncianteService::class);
        $service->shouldReceive('getDados')->with(1)->andReturn(['nome' => 'Teste']);
        $controller = new \App\Modules\Anunciante\Controllers\AnuncianteController($service);
        $response = $controller->getDados(1);
        // Exibe os dados passados e recebidos
        fwrite(STDERR, "\n[DADOS PASSADOS] id: 1\n");
        fwrite(STDERR, "[DADOS RECEBIDOS] " . json_encode($response) . "\n");
        $this->assertEquals(['nome' => 'Teste'], $response);
    }

    public function test_post_dados_envia_dados_para_service()
    {
        $service = \Mockery::mock(\App\Modules\Anunciante\Services\AnuncianteService::class);
        $service->shouldReceive('postDados')->with(1, ['nome' => 'Novo'])->andReturn(['ok' => true]);
        $controller = new \App\Modules\Anunciante\Controllers\AnuncianteController($service);
        $request = \Mockery::mock('App\\Modules\\Anunciante\\Requests\\AnuncianteDadosRequest');
        $request->shouldReceive('validated')->andReturn(['nome' => 'Novo']);
        $response = $controller->postDados($request, 1);
        // Exibe os dados passados e recebidos
        fwrite(STDERR, "\n[DADOS PASSADOS] id: 1, dados: " . json_encode(['nome' => 'Novo']) . "\n");
        fwrite(STDERR, "[DADOS RECEBIDOS] " . json_encode($response) . "\n");
        $this->assertEquals(['ok' => true], $response);
    }

    // public function test_post_midia_envia_arquivo_para_service()
    // {
    //     Storage::fake('local');
    //     $service = Mockery::mock(AnuncianteService::class);
    //     $service->shouldReceive('postMidia')->andReturn(['status' => 'ok']);
    //     $controller = new AnuncianteController($service);
    //     $request = Mockery::mock('App\Modules\Anunciante\Requests\AnuncianteMidiaRequest');
    //     $request->shouldReceive('file')->with('file')->andReturn(UploadedFile::fake()->image('foto.jpg'));
    //     $request->shouldReceive('input')->with('tipo')->andReturn('imagem');
    //     $response = $controller->postMidia($request, 1);
    //     $this->assertEquals(['status' => 'ok'], $response);
    // }
}
