<?php

declare(strict_types=1);

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Api
{
    private static ?Client $client = null;

    public function __construct(
        string $baseUri,
        array $headers = [],
        float $timeout = 60.0
    ) {
        if (self::$client === null) {
            self::$client = new Client([
                'base_uri' => rtrim($baseUri, '/'),
                'timeout' => $timeout,
                'headers' => array_merge([
                    'Accept' => 'application/json',
                    'User-Agent' => sprintf(
                        'MusaClass/1.0 (PHP %s; %s; %s)',
                        PHP_VERSION,
                        PHP_OS,
                        $_SERVER['SERVER_NAME'] ?? 'local'
                    ),
                ], $headers),
            ]);
        }
    }

    public static function get(string $uri, array $options = []): string
    {
        try {
            $response = self::$client->get($uri, $options);
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new \RuntimeException(
                "Erro ao fazer requisição GET para {$uri}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public static function post(string $uri, array $payload = [])
    {
        try {
         
            $response = self::$client->post($uri, ['json' => $payload]);
            
            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new \RuntimeException('erro ao criar cobrança'. $e->getMessage());
        }
    }
}
