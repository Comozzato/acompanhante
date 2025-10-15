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
            $this->refreshHistoryCache();
        }
    }
  
    protected function refreshHistoryCache()
    {
        // Exemplo: limpar o cache
        $service = app(HistoryService::class);
        Cache::forget('historys_feed');
        Cache::put('historys_feed', $service->getHistorys(), 60 * 60); // Cache por 1 hora
        logger('✅ Cache de historys atualizado após aprovação de feed.');
    }
}
