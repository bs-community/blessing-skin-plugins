<?php

namespace Yggdrasil\Middleware;

class CheckContentType
{
    public function handle($request, \Closure $next)
    {
        if (!$request->isJson()) {
            return json([
                'error' => 'Unsupported Media Type',
                'errorMessage' => trans('Yggdrasil::middleware.content-type'),
            ]);
        }

        return $next($request);
    }
}
