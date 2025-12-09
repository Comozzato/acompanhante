<?php

declare(strict_types=1);

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Asaas
{
    private static ?Client $client = null;

    public static function init(): self
    {   
        info('Initializing Asaas Client');
        Self::$client = new Client([
                'base_uri' => rtrim(config('services.asaas.url'), '/'),
                'timeout' => 60.0,
                'headers' => array_merge([
                    'Accept' => 'application/json',
                    'User-Agent' => sprintf(
                        'MusaClass/1.0 (PHP %s; %s; %s)',
                        PHP_VERSION,
                        PHP_OS,
                        $_SERVER['SERVER_NAME'] ?? 'local'
                    ),
                ], [
                    'access_token' => config('services.asaas.token'),
                ]),
            ]);
        return new self();
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
