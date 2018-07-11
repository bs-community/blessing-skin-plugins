<?php

namespace Yggdrasil\Middleware;

use Cache;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class Throttle
{
    public function handle($request, \Closure $next)
    {
        $id = sprintf('YGG_LAST_REQ_%s', $request->get('username'));
        $currentTimeInMillisecond = microtime(true) * 1000;
        $retryAfter = option('ygg_rate_limit') - ($currentTimeInMillisecond - Cache::get($id));

        if ($retryAfter > 0) {
            throw new ForbiddenOperationException(sprintf('请求过于频繁，请等待 %d 秒后重试', ceil($retryAfter / 1000)));
        }

        Cache::put($id, $currentTimeInMillisecond, 60);

        return $next($request);
    }
}
