<?php

declare(strict_types=1);

namespace App\Helpers;

use Exception;

class AsaasApi
{
    private static Api $api;

    public function __construct()
    {
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

    public static function Assas(): Api
    {
        return self::$api;
    }
}
