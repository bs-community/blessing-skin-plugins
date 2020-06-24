<?php

namespace Yggdrasil\Middleware;

use Illuminate\Http\Response;

class AddApiIndicationHeader
{
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        if ($response instanceof Response) {
            // @see https://github.com/yushijinhun/authlib-injector/issues/18
            $response->headers->set(
                'X-Authlib-Injector-API-Location',
                url('api/yggdrasil')
            );
        }

        return $response;
    }
}
