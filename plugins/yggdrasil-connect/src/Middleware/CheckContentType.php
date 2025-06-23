<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckContentType
{
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->isJson()) {
            return json([
                'error' => 'Unsupported Media Type',
                'errorMessage' => trans('LittleSkin\YggdrasilConnect::middleware.content-type'),
            ]);
        }

        return $next($request);
    }
}
