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
        // Verifica se o campo 'publish' mudou e foi aprovado
        if ($feed->wasChanged('publish') && $feed->publish === 'Aprovado') {
            // Aqui você atualiza seu cache de Historys
            Cache::tags(['historys'])->flush();
        }
    }
}
