<?php

declare(strict_types=1);

namespace App\Helpers;

use Exception;

class AsaasApi
{
    private static Api $api;

    public function __construct()
    {

        if (self::$api === null) {
            $uri = env('URL_SANDBOX_ASAAS');
            $token = env('ACCESS_TOKEN');
            if (empty($uri)) {
                throw new Exception('URL do serviço Asaas está vazia', 15);
            }

            if (empty($token)) {
                throw new Exception('Token de acesso do Asaas está vazio', 16);
            }
            self::$api = new Api($uri, [
                'access_token' => $token
            ]);
        }
    }

    public static function api(): Api
    {
        if (!isset(self::$api)) {
            $uri = env('URL_SANDBOX_ASAAS');
            $token = env('ACCESS_TOKEN');

            if (empty($uri)) {
                throw new \RuntimeException('URL do serviço Asaas está vazia.');
            }

            if (empty($token)) {
                throw new \RuntimeException('Token de acesso Asaas está vazio.');
            }

            self::$api = new Api($uri, [
                'access_token' => "{$token}",
                'User-Agent' => sprintf(
                    'MusaClass/1.0 (PHP %s; %s; %s)',
                    PHP_VERSION,
                    PHP_OS,
                    $_SERVER['SERVER_NAME'] ?? 'local'
                ),
            ]);
        }
        return self::$api;
    }
}
