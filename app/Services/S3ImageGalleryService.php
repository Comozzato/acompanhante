<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Importar a classe Str

class S3ImageGalleryService
{
    protected $disk = 's3';
    protected $basePath = 'gallery/'; // Diretório base para as imagens da galeria no S3

    /**
     * Faz upload de uma imagem para o S3.
     *
     * @param UploadedFile $file O arquivo da imagem.
     * @param string|null $folder Subpasta opcional dentro da galeria.
     * @return string|false O caminho do arquivo no S3 ou false em caso de falha.
     */
    public function uploadImage(string $path, $id, string $folder = null): string|false
    {
        if (!file_exists($path)) {
            Log::error('Arquivo não encontrado para upload: ' . $path);
            return false;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;
        $s3Path = $id . '/' . $this->basePath . ($folder ? trim($folder, '/') . '/' : '') . $fileName;

        try {
            Storage::disk($this->disk)->put($s3Path, file_get_contents($path));
            if (!Storage::disk($this->disk)->exists($s3Path)) {
                Log::error('arquivo não foi encontrado no S3');
            }
            return $s3Path;
        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload para o S3: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista imagens de uma pasta específica na galeria.
     *
     * @param string|null $folder Subpasta opcional.
     * @return array Lista de URLs das imagens.
     */
    public function listImages(string $folder = null): array
    {
        $path = $this->basePath . ($folder ? trim($folder, '/') . '/' : '');
        $files = Storage::disk($this->disk)->files($path);

        return array_map(function ($file) {
            return Storage::disk($this->disk)->url($file);
        }, $files);
    }

    /**
     * Retorna a URL temporária para exibir uma imagem específica.
     * Ideal para arquivos privados, mas também funciona para públicos.
     *
     * @param string $filePath Caminho completo do arquivo no S3 (ex: gallery/imagem.jpg).
     * @param int $minutes Duração da URL temporária em minutos.
     * @return string|null URL da imagem ou null se o arquivo não existir.
     */
    public function getImage(string $filePath, int $minutes = 5): ?string
    {
        if (Storage::disk($this->disk)->exists($filePath)) {
            // Para buckets privados, use temporaryUrl
            // return Storage::disk($this->disk)->temporaryUrl($filePath, now()->addMinutes($minutes));
            // Para buckets públicos, a URL direta é suficiente
            return Storage::disk($this->disk)->get($filePath);
        }
        return null;
    }

    /**
     * Deleta uma imagem do S3.
     *
     * @param string $filePath Caminho completo do arquivo no S3 (ex: gallery/imagem.jpg).
     * @return bool True se deletado com sucesso, false caso contrário.
     */
    public function deleteImage(string $filePath): bool
    {
        if (Storage::disk($this->disk)->exists($filePath)) {
            return Storage::disk($this->disk)->delete($filePath);
        }
        return false;
    }

    /**
     * Busca imagens na galeria.
     * (Implementação básica, pode ser expandida com metadados ou busca mais avançada)
     *
     * @param string $searchTerm Termo de busca (ex: nome do arquivo).
     * @param string|null $folder Subpasta opcional.
     * @return array Lista de URLs das imagens encontradas.
     */
    public function searchImages(string $searchTerm, string $folder = null): array
    {
        $path = $this->basePath . ($folder ? trim($folder, '/') . '/' : '');
        $allFiles = Storage::disk($this->disk)->files($path);

        $filteredFiles = array_filter($allFiles, function ($file) use ($searchTerm) {
            return Str::contains(basename($file), $searchTerm);
        });

        return array_map(function ($file) {
            return Storage::disk($this->disk)->url($file);
        }, $filteredFiles);
    }
}