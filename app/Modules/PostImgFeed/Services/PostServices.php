<?php

declare(strict_types=1);

namespace App\Modules\PostImgFeed\Services;

use App\Models\Feed;
use App\Models\Midia;
use App\Modules\PostImgFeed\Services\Treantment;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        if (!$dataRequest['post_id']) {
            throw new \Exception('Post ID is required');
        }
        $post = [
            'user_id' => auth_user()->id,
            'post' => $dataRequest['post'],
            'post_id' => $dataRequest['post_id'],
            'ativo' => true,
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
    }
}
