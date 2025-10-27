<?php

namespace App\Console\Commands;

use App\Models\Feed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class RezisedThumbVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rezised-video';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';



    /**
     * Execute the console command.
     */
    public function handle()
    {
        $manager = new ImageManager(new Driver());

        // Traz feeds com as mídias
        $feeds = Feed::with('midia')
            ->where('tipo_arquivo', 'video')
            ->get();

        $totalMidias = $feeds->sum(function ($feed) {
            return $feed->midia->filter(function ($midia) {
                return strtolower(pathinfo($midia->midia, PATHINFO_EXTENSION)) !== 'mp4';
            })->count();
        });

        // Criar a barra de progresso
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, $totalMidias);
        $progressBar->start();

        $feeds->each(function ($feed) use ($manager, $progressBar) {
            $feed->midia
                ->filter(function ($midia) {
                    return strtolower(pathinfo($midia->midia, PATHINFO_EXTENSION)) !== 'mp4';
                })
                ->each(function ($midia) use ($manager, $progressBar) {
                    // Baixar do S3
                    $conteudo = Storage::disk('s3')->get($midia->midia);

                    // Garantir diretório temporário
                    $tempDir = storage_path('app/private/tmp');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0777, true);
                    }

                    // Caminho local
                    $localPath = $tempDir . '/' . basename($midia->midia);
                    file_put_contents($localPath, $conteudo);
                    
                    if (!@getimagesize($localPath)) {
                        echo "⚠ Ignorado: {$midia->midia} não é imagem válida.\n";
                        unlink($localPath);
                        return;
                    }

                    // Processar imagem
                    $image = $manager->read($localPath);
                    $image->resize(480, 848);
                    $image->toPng()->save($localPath);

                    // Reenviar pro S3
                    Storage::disk('s3')->put($midia->midia, file_get_contents($localPath));

                    // Apagar temporário
                    unlink($localPath);

                    // Avançar progress bar
                    $progressBar->advance();
                });
        });

        $progressBar->finish();
        $this->info("\n✅ Processamento concluído!");
    }
}
