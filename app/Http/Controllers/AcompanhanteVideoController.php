<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\WpApi;
use Illuminate\Http\Request;

class AcompanhanteVideoController extends Controller
{   
    public function __construct(public WpApi $Api) {}

    public function index($postId)
    {   
        //return now()->timestamp;
        $res = $this->Api->get("/wp-json/musaclass/v1/acompanhante/{$postId}/videos", [
            't' => now()->timestamp
        ]);

        return response()->json($res->json(), $res->status());
    }

    public function upload(Request $request, $postId)
    {   
        $request->validate([
            'file' => 'required|file|mimes:mp4,mov,avi,webm|max:512000',
        ]);

        $file = $request->file('file');
        //return $file;
        try{
             $this->Api->postMultipart(
            "/wp-json/musaclass/v1/acompanhante/". $postId ."/videos/upload",
            'file',
            $file->getRealPath(),
            $file->getClientOriginalName()
        );
        }catch(\Throwable $e)
        {
            // ignora o retorno 
        }

        return response()->json([
                                'success' => true,
                                 'message' => 'Upload enviado para processamento'
                                ], 202);
    }

    public function delete(Request $request, $postId)
    {      
        $url = $request->url;
        $res = $this->Api->post("/acompanhantes/" . $postId. '/videos/remove',[
            'url' => $url
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
    public function reorder(Request $request, $postId)
    {
        $request->validate([
            'ordem' => 'required|array',
            'ordem.*' => 'string',
        ]);

        $res = $this->Api->post(
            "/wp-json/musaclass/v1/acompanhante/{$postId}/videos/reorder",
            ['ordem' => $request->ordem]
        );

        return response()->json($res->json(), $res->status());
    }
}