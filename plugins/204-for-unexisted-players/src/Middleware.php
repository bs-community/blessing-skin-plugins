<?php

namespace Blessing\NoContent;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Middleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Response */
        $response = $next($request);

        if ($response->status() === 404) {
            return response()->noContent();
        }

        return $response;
    }
}
