<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HandleCors
{
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->is('yggc/userinfo')) {
            return $next($request);
        }

        if ($this->isPreflightRequest($request)) {
            return response(null)->setStatusCode(Response::HTTP_NO_CONTENT)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, HEAD')
                ->header('Access-Control-Allow-Headers', '*');
        }

        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');

        return $response;
    }

    private function isPreflightRequest(Request $request): bool
    {
        return $request->isMethod('OPTIONS') && ($request->headers->has('Access-Control-Request-Method') || $request->headers->has('Access-Control-Request-Headers'));
    }
}
