<?php

namespace Yggdrasil\Middleware;

use Cache;
use Closure;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class Throttle
{
    public function handle($request, Closure $next)
    {
        $id = sprintf('YGG_LAST_REQ_%s', $request->input('username'));
        $currentTimeInMillisecond = microtime(true) * 1000;
        $retryAfter = option('ygg_rate_limit') - ($currentTimeInMillisecond - Cache::get($id));

        if ($retryAfter > 0) {
            throw new ForbiddenOperationException(
                trans('Yggdrasil::middleware.throttle', ['s' =>  ceil($retryAfter / 1000)])
            );
        }

        Cache::put($id, $currentTimeInMillisecond, 3600);

        return $next($request);
    }
}
