<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;
use LittleSkin\YggdrasilConnect\Models\AccessToken;

// 这个中间件是给设置 Auth Server 和 Session Server 使用的
// 给设置材质 API 使用的中间件看 CheckBearerTokenYggdrasil
class CheckAccessToken
{
    public function handle(Request $request, \Closure $next)
    {
        $validation = Validator::make($request->all(), [
            'accessToken' => ['required', 'string'],
            'clientToken' => ['nullable', 'string'],
            'requestUser' => ['nullable', 'boolean']
        ]);

        if (!$validation->fails()) {
            $accessToken = $request->input('accessToken');
            try {
                Log::channel('ygg')->info("User is authenticating with Access Token", [$accessToken]);
                $token = new AccessToken($accessToken);
                $valid = $request->is('api/yggdrasil/authserver/refresh') ? $token->isRefreshable() : $token->canJoinServer();
                if ($valid) {
                    Auth::setUser($token->owner);
                    return $next($request);
                } else {
                    throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.invalid'));
                }
            } catch (ForbiddenOperationException $e) {
                Log::channel('ygg')->info("Access Token [$accessToken] is invalid", [$e->getMessage()]);
                if ($request->is('api/yggdrasil/authserver/invalidate')) {
                    // invalidate 请求不管 Access Token 是否有效，都直接返回 204
                    return response()->noContent();
                }
                throw $e;
            }
        }

        throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::exceptions.illegal'));
    }
}
