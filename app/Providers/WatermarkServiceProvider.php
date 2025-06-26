<?php

namespace App\Providers;


use App\Enums\ImagesFeed;
use Illuminate\Support\ServiceProvider;
use App\Modules\PostImgFeed\Services\Strategies\MasterImageWaterMark;
use App\Modules\PostImgFeed\Services\WatermarkStrategy;
use Illuminate\Foundation\Application;
class WatermarkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function boot(): void
    {
        $this->app->singleton(WatermarkStrategy::class, function (Application $app) {
            $strategies = [
                // Instâncias, não nomes de classes!
                new MasterImageWaterMark()
            ];

            return new WatermarkStrategy($strategies);
        });
    }
}

