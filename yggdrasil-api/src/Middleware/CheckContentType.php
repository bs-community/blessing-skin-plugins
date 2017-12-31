<?php

namespace Yggdrasil\Middleware;

class CheckContentType
{
    public function handle($request, \Closure $next)
    {
        if (! $request->isJson()) {
            return json([
                'error' => 'Unsupported Media Type',
                'errorMessage' => '请求的 Content-Type 必须为 application/json'
            ]);
        }

        return $next($request);
    }
}
