<?php

namespace Yggdrasil\Middleware;

use Illuminate\Http\Response;

class AddApiIndicationHeader
{
    public function handle($request, \Closure $next)
    {
        /** @var Response */
        $response = $next($request);
        $response->header('X-Authlib-Injector-API-Location', url('api/yggdrasil'));

        return $response;
    }
}
