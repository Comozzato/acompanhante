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
        $historys = Cache::get('historys_feed_' . $city);
        if (!$historys) {
            $historys = $this->service->getHistorys();
            Cache::put('historys_feed_' . $city, $historys, 60 * 60); // Cache por 1 hora
            logger('✅ Cache de historys criado.');
        }

        return response()->json($historys);
    }
}
