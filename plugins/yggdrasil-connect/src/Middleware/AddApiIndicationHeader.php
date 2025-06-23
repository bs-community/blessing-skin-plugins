<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddApiIndicationHeader
{
    public function handle(Request $request, \Closure $next)
    {
        /** @var Response */
        $response = $next($request);

        // Symfony way
        $response->headers->set('X-Authlib-Injector-API-Location', url('api/yggdrasil'));

        return $response;
    }
}
