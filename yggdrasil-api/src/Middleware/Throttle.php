<?php

namespace Yggdrasil\Middleware;

use Cache;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class Throttle
{
    public function handle($request, \Closure $next)
    {
        $id = "YGG_LAST_REQ_".$request->get('username');
        $retryAfter = option('ygg_rate_limit') - (time() - Cache::get($id));

        if ($retryAfter > 0) {
            throw new ForbiddenOperationException("请求过于频繁，请等待 $retryAfter 秒后重试");
        }

        Cache::put($id, time(), 60);

        return $next($request);
    }
}
