<?php

declare(strict_types=1);

namespace App\Modules\History;

use App\Http\Resources\Historys;
use App\Models\Feed;
use App\Models\Posts;

class HistoryService
{
    //

    public function __construct(private Posts $posts) {}

    public function getHistorys()
    {
        $feeds = $this->posts
            ->with('feedslimits3.midia')
            ->publish()
            ->city(request()->query('city'))
            ->orderByRaw('cidade_virtual ASC') // false (0) primeiro, true (1) por último
            ->orderByDesc(
                Feed::select('publicado_em')
                    ->whereColumn('post_id', 'posts.id')
                    ->orderByDesc('publicado_em')
                    ->limit(1)
            )
            ->get();
        return [

            'circles' => array_values(array_filter(
                Historys::collection($feeds)->toArray(request()),
                fn($item) => !empty($item)
            )),
        ];
    }
}
