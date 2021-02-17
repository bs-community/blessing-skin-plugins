<?php

namespace Yggdrasil\Middleware;

use Yggdrasil\Exceptions\UnauthorizedException;
use Yggdrasil\Models\Token;

class CheckBearerToken
{
    public function handle($request, \Closure $next)
    {
        $accessToken = $request->bearerToken();
        if (!$accessToken) {
            throw new UnauthorizedException(trans('Yggdrasil::exceptions.token.bearer'));
        }

        $token = Token::find($accessToken);
        if ($token && $token->isValid()) {
            return $next($request);
        }

        throw new UnauthorizedException(trans('Yggdrasil::exceptions.token.bearer'));
    }
}
