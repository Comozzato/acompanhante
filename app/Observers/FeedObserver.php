<?php

namespace App\Observers;

use App\Models\Feed;
use App\Modules\History\HistoryService;
use Illuminate\Support\Facades\Cache;

class FeedObserver
{
    //
    public function updated(Feed $feed)
    {
        if ($feed->wasChanged('publish') && $feed->publish === 'Aprovado') {
            $keys = Cache::get('historys_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget('historys_keys'); // limpa a lista
            info('🧹 Todos os caches de historys limpos.');
        }
    }
}
