<?php

namespace App\Http\Controllers;

use App\Helpers\WpApi;
use Illuminate\Http\Request;

class GaleryAcompanhanteController extends Controller
{
    //
    public function __construct(public WpApi $Api) {}

    public function index($postId)
    {
        $this->ensureAcompanhanteExists($postId);

        $res = $this->Api->get('/wp-json/musaclass/v1/acompanhante/' . $postId . '/images');

        if (!$res->successful()) {
            return response()->json([
                'message' => 'Falha ao buscar imagens do acompanhante no WordPress',
                'wp_status' => $res->status(),
                'wp_body' => $res->json(),
            ], 500);
        }

        return $res->json();
    }


    public function upload(Request $request, int $postId)
    {
        $this->ensureAcompanhanteExists($postId);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:10240',
        ]);

        $file = $request->file('file');

        // nome seguro
        $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '-', $file->getClientOriginalName());
        $safeName = preg_replace('/-+/', '-', $safeName);

        $binary = file_get_contents($file->getRealPath());

        // 1) upload cria attachment
        $uploadRes = $this->Api->uploadMedia(
            $safeName,
            $binary,
            $file->getMimeType()
        );

        if (!$uploadRes->successful()) {
            return response()->json([
                'message' => 'Falha ao fazer upload da imagem no WordPress',
                'wp_status' => $uploadRes->status(),
                'wp_body' => $uploadRes->json(),
            ], 500);
        }

        $media = $uploadRes->json();
        $mediaId = $media['id'];

        // 2) buscar quantos attachments já existem pra calcular menu_order final
        $listRes = $this->Api->get('/wp-json/wp/v2/media', [
            'parent' => $postId,
            'media_type' => 'image',
            'per_page' => 100,
        ]);

        $nextOrder = 0;
        if ($listRes->successful()) {
            $nextOrder = count($listRes->json());
        }

        // 3) anexa ao post acompanhante e seta menu_order
        $updateRes = $this->Api->post('/wp-json/wp/v2/media/' . $mediaId, [
            'post' => $postId,
            'menu_order' => $nextOrder,
        ]);

        if (!$updateRes->successful()) {
            // upload foi feito, mas não anexou/ordenou
            return response()->json([
                'message' => 'Upload feito, mas falhou ao anexar no acompanhante (post/menu_order)',
                'media' => $media,
                'wp_status' => $updateRes->status(),
                'wp_body' => $updateRes->json(),
            ], 200);
        }

        $updated = $updateRes->json();

        $thumb = $updated['media_details']['sizes']['thumbnail']['source_url'] ?? $updated['source_url'];
        $medium = $updated['media_details']['sizes']['medium']['source_url'] ?? $updated['source_url'];

        return response()->json([
            'item' => [
                'id' => $updated['id'],
                'url' => $updated['source_url'],
                'thumb' => $thumb,
                'medium' => $medium,
                'title' => $updated['title']['rendered'] ?? '',
                'menu_order' => $updated['menu_order'] ?? $nextOrder,
            ],
        ]);
    }

    public function reorder(Request $request, int $postId)
    {
        $this->ensureAcompanhanteExists($postId);

        $request->validate([
            'ordered_ids' => 'required|array|min:1',
            'ordered_ids.*' => 'integer',
        ]);

        
        info($request->ordered_ids);
        
        $res = $this->Api->post('/wp-json/musaclass/v1/acompanhante/' . $postId . '/images/reorder', [
            'ordered_ids' => $request->ordered_ids
        ]);

        if(!$res->successful())
        {
            info('Error: '. $res->json());
            return response()->json(['message'=>'erro ao reordena as imagens'],400);
        }

        return $res->json();
    }

    public function detach(int $postId, int $mediaId)
    {
        $this->ensureAcompanhanteExists($postId);

        $res = $this->Api->post('/wp-json/wp/v2/media/' . $mediaId, [
            'post' => 0,
        ]);

        if (!$res->successful()) {
            return response()->json([
                'message' => 'Falha ao desanexar imagem do acompanhante',
                'wp_status' => $res->status(),
                'wp_body' => $res->json(),
            ], 500);
        }

        return response()->json(['ok' => true]);
    }
    private function ensureAcompanhanteExists(int $postId): array
    {
        $res = $this->Api->get('/wp-json/wp/v2/acompanhante/' . $postId);

        if (!$res->successful()) {
            abort(response()->json([
                'message' => 'Acompanhante não encontrado no WordPress',
                'post_id' => $postId,
                'wp_status' => $res->status(),
                'wp_body' => $res->json(),
            ], 404));
        }

        return $res->json();
    }

    public function delete(int $postId, int $mediaId)
    {
        $this->ensureAcompanhanteExists($postId);

        $res = $this->Api->delete('/wp-json/wp/v2/media/' . $mediaId, [
            'force' => true,
        ]);

        if (!$res->successful()) {
            return response()->json([
                'message' => 'Falha ao apagar imagem do WordPress',
                'wp_status' => $res->status(),
                'wp_body' => $res->json(),
            ], 500);
        }

        return response()->json(['ok' => true]);
    }
}
