<?php

namespace App\Providers;

use App\Helpers\Api;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AsaasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(Api::class, function ($app) {
            $uri = config('services.asaas.url');
            $token = config('services.asaas.token');
            if (empty($uri)) {
                throw new RuntimeException('URL do serviço Asaas está vazia.');
            }

            if (empty($token)) {
                throw new RuntimeException('Token de acesso Asaas está vazio.');
            }
            return new Api($uri, [
                'access_token' => "{$token}",
                'User-Agent' => sprintf(
                    'MusaClass/1.0 (PHP %s; %s; %s)',
                    PHP_VERSION,
                    PHP_OS,
                    request()->getHost() ?? 'local'
                ),
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
