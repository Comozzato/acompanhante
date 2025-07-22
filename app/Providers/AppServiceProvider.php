<?php

namespace App\Providers;

use App\Modules\PostImgFeed\Services\Strategies\MasterImageWaterMark;
use App\Modules\PostImgFeed\Services\Strategies\ThumbNailPrimaryWaterMark;
use App\Modules\PostImgFeed\Services\Strategies\ThumbNailSecundaryWaterMark;
use App\Modules\PostImgFeed\Services\WatermarkStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(WatermarkStrategy::class, function ($app) {
            return new WatermarkStrategy([
                new MasterImageWaterMark(
                    $app->make(\App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer::class),
                    $app->make(\App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier::class),
                ),
                new ThumbNailPrimaryWaterMark(
                    $app->make(\App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer::class),
                    $app->make(\App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier::class),
                ),
                new ThumbNailSecundaryWaterMark(
                    $app->make(\App\Modules\PostImgFeed\Services\ImageProcessing\ImageResizer::class),
                    $app->make(\App\Modules\PostImgFeed\Services\ImageProcessing\WatermarkApplier::class)
                )
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
