<?php

declare(strict_types=1);

namespace App\Http\Controllers\Historys;

use App\Modules\History\HistoryService;
use Illuminate\Support\Facades\Cache;

class HistorysController extends \App\Http\Controllers\Controller
{
    //

    public function __construct(private HistoryService $service) {}


    public function index()
    {
        $city = request()->query('city');
        $key = 'historys_feed_' . $city;
        // Tenta recuperar do cache
        $historys = Cache::get($key);


        if (!$historys) {
            // Busca do serviço
            $historys = $this->service->getHistorys();
            // Salva no cache principal
            Cache::put($key, $historys, 3600); // 1 hora

            // Atualiza a lista de chaves de historys
            $keys = Cache::get('historys_keys', []);
            if (!in_array($key, $keys)) {
                $keys[] = $key;
                Cache::put('historys_keys', $keys, 3600);
            }

            info('✅ Cache de historys criado.');
        }

        return response()->json($historys);
    }
}
