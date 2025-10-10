<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services;

use App\Models\Feed;
use App\Models\Midia;
use App\Modules\PostImgFeed\Services\Treantment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PostServices
{
    // This class is a placeholder for the PostServices class.
    // It can be used to define methods related to post services.
    // Currently, it does not contain any methods or properties.

    public function __construct(private Treantment $treantment)
    {
        // Constructor can be used to initialize any dependencies or properties.
    }

    public function post($dataRequest, $file)
    {
        try {
            // Start a database transaction
            DB::beginTransaction();
            if (!$dataRequest['post_id']) {
                throw new \Exception('Post ID is required');
            }

            $fileType = $file ? $file->getClientMimeType() : null;

            $imageMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $videoMimes = ['video/mp4', 'video/mkv', 'video/avi', 'video/mov'];

            if (in_array($fileType, $imageMimes)) {
                $tipoMidia = 'imagem';
            } elseif (in_array($fileType, $videoMimes)) {
                $tipoMidia = 'video';
            } else {
                throw new \Exception("Tipo de arquivo não suportado: {$fileType}");
            }

            if ($dataRequest['tipo'] === 'story') {
                $expiraEm = now()->addHours(72);
                Gate::forUser(auth_user())->allows('post-limit', $tipoMidia, $dataRequest['post_id']);
            }


            $post = [
                'user_id' => auth_user()->id,
                'post' => $dataRequest['post'],
                'post_id' => $dataRequest['post_id'],
                'ativo' => true,
                'tipo' => $dataRequest['tipo'] ?? 'post',
                'tipo_arquivo' => $tipoMidia,
                'expires_at' => $expiraEm ?? null,
                'publicado_em' => now()->setTimezone('America/Sao_Paulo')
            ];
            $feed = Feed::create($post);
            if ($file) {
                // Processa a imagem e aplica as marcas d'água
                $paths = $this->treantment->processImageFeed($file);
                foreach ($paths as $path) {
                    Midia::create([
                        'feed_id' => $feed->id,
                        'midia' => $path
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the actual error that caused the transaction to fail
            Log::error("Transaction failed: " . $e->getMessage());
            // You can also re-throw the exception or handle it as needed
            throw $e;
        }
    }
}
