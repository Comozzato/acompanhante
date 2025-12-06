<?php

declare(strict_types=1);

namespace App\Resolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class UserResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null)
    {

        info('UserResolver called', [
            'request_user_id' => request()->user_id,
            'all_request_data' => request()->all()
        ]);
        if (request()->has('user_id')) {
            return request()->user_id;
        }

        // Ou de headers, se você usa
        if (request()->hasHeader('X-User-ID')) {
            return request()->header('X-User-ID');
        }

        // Ou de algum middleware que definiu na request
        if (request()->attributes->has('user_id')) {
            return request()->attributes->get('user_id');
        }

        // Se não encontrar, retorna null
        return null;
    }
}
