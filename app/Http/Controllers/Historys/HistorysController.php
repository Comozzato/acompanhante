<?php

declare(strict_types=1);

namespace App\Http\Controllers\Historys;

use App\Modules\History\HistoryService;

class HistorysController extends \App\Http\Controllers\Controller
{
    //

    public function __construct(private HistoryService $service) {}


    public function index()
    {
        $historys = $this->service->getHistorys();
        return response()->json($historys);
    }
}
