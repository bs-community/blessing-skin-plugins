<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\Request;
use LittleSkin\YggdrasilConnect\Exceptions\OAuth\InvalidScopeException;
use LittleSkin\YggdrasilConnect\Scope;

class CheckIfScopeValid
{
    public function handle(Request $request, \Closure $next)
    {
        if ($scope = $request->input('scope')) {
            $scopes = explode(' ', $scope);
            if ((
                array_intersect($scopes, Scope::OIDC_SCOPES) && !in_array(Scope::OPENID, $scopes))
                || (in_array(Scope::PROFILE_SELECT, $scopes) && in_array(Scope::PROFILE_READ, $scopes))
                || (in_array(Scope::SERVER_JOIN, $scopes) && !in_array(Scope::PROFILE_SELECT, $scopes))
            ) {
                $exception = new InvalidScopeException();
                $query = $exception->toArray();
                $query['state'] = $request->input('state');

                return redirect()->away($request->input('redirect_uri').'?'.http_build_query($query));
            }
        }

        return $next($request);
    }
}
