<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class WpApi
{
    private string $baseUrl;
    private string $username;
    private string $appPassword;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.anuncio_api.url'), '/');
        $this->username = config('services.anuncio_api.user');
        $this->appPassword = config('services.anuncio_api.pass');
    }

    private function client()
    {
        return Http::withBasicAuth($this->username, $this->appPassword)
            ->acceptJson();
    }

    public function get(string $endpoint, array $query = [])
    {
        return $this->client()->get($this->baseUrl . $endpoint, $query);
    }

    public function post(string $endpoint, array $data = [])
    {
        return $this->client()->post($this->baseUrl . $endpoint, $data);
    }


    public function uploadMedia(string $filename, string $fileBinary, string $mimeType)
    {
        return $this->client()
            ->withHeaders([
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Type'        => $mimeType,
        ])
        ->withBody($fileBinary, $mimeType)
        ->send(
            'POST',
            $this->baseUrl . '/wp-json/wp/v2/media'
        );
    }

    public function delete(string $endpoint, array $query = [])
    {
        return $this->client()->delete($this->baseUrl . $endpoint, $query);
    }

    
}
