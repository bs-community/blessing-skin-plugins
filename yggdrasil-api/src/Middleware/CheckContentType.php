<?php

namespace Yggdrasil\Middleware;

class CheckContentType
{
    public function handle($request, \Closure $next)
    {
        if (! $request->isJson()) {
            return json([
                'error' => 'Unsupported Media Type',
                'errorMessage' => 'The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method'
            ]);
        }

        return $next($request);
    }
}
