<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\InsufficientScopeException;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\InvalidTokenException;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\UnauthorizedException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;
use LittleSkin\YggdrasilConnect\Models\AccessToken;

class CheckBearerTokenOAuth
{
    public function handle(Request $request, \Closure $next, ?string $scope = null)
    {
        if ($bearerToken = $request->bearerToken()) {
            try {
                $token = new AccessToken($bearerToken);
                if ($token->isValid()) {
                    if (empty($scope) || $token->can($scope)) {
                        Auth::setUser($token->owner);
                        return $next($request);
                    } else {
                        throw new InsufficientScopeException(scope: $scope);
                    }
                }
            } catch (ForbiddenOperationException | IllegalArgumentException $e) {
                throw new InvalidTokenException(scope: $scope);
            }
        }

        throw new UnauthorizedException(scope: $scope);
    }
}
