<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Importar a classe Str

class S3ImageGalleryService
{
    protected static $disk = 's3';
    protected $basePath = 'gallery/'; // Diretório base para as imagens da galeria no S3

    /**
     * Faz upload de uma imagem para o S3.
     *
     * @param UploadedFile $file O arquivo da imagem.
     * @param string|null $folder Subpasta opcional dentro da galeria.
     * @return string|false O caminho do arquivo no S3 ou false em caso de falha.
     */
    public static function uploadImage(string $path, string $file): string|false
    {
        try {
            Storage::disk(self::$disk)->put($path, file_get_contents($file));
            if (!Storage::disk(self::$disk)->exists($path)) {
                Log::error('arquivo não foi encontrado no S3');
            }
            return $path;
        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload para o S3: ' . $e->getMessage());
            return false;
        }
    
    }

    public static function getImage(string $filePath, int $minutes = 5): ?string
    {
        if (Storage::disk(self::$disk)->exists($filePath)) {
            // Para buckets privados, use temporaryUrl
            return Storage::disk(self::$disk)->temporaryUrl($filePath, now()->addMinutes(60));
            // Para buckets públicos, a URL direta é suficiente
            //return Storage::disk(self::$disk)->get($filePath);
        }
        return null;
    }

    public static function deleteImage(string $filePath): bool
    {
        if (Storage::disk(self::$disk)->exists($filePath)) {
            return Storage::disk(self::$disk)->delete($filePath);
        }
        return false;
    }

}