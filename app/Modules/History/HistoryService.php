<?php

declare(strict_types=1);

namespace App\Modules\History;

use App\Http\Resources\Historys;
use App\Models\Feed;
use App\Models\Posts;
use Illuminate\Http\Request;

class HistoryService
{
    //

    public function __construct(private Posts $posts) {}

    public function getHistorys()
    {
        $feeds = $this->posts
            ->with('feeds.midia')
            ->city(request()->query('city'))
            ->get();
        //dd($feeds->toArray());
        return [
            'circles' => array_filter(
                Historys::collection($feeds)->toArray(request()),
                fn($item) => !empty($item)
            ),
        ];
    }
}
