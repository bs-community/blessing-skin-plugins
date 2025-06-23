<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\UnauthorizedException;
use LittleSkin\YggdrasilConnect\Models\AccessToken;

// 这个中间件是给设置 /api/user/profile/{uuid}/{textureType} 使用的
// 给 Auth Server 和 Session Server 使用的中间件看 CheckAccessToken
class CheckBearerTokenYggdrasil
{
    public function handle(Request $request, \Closure $next)
    {
        if ($accessToken = $request->bearerToken()) {
            Log::channel('ygg')->info('User is authenticating with Access Token', [$accessToken]);
            try {
                $token = new AccessToken($accessToken);
                if ($token->canJoinServer() && $token->selectedProfile == $request->route('uuid')) {
                    Auth::setUser($token->owner);

                    return $next($request);
                }
            } catch (ForbiddenOperationException $e) {
                Log::channel('ygg')->info("Access Token [$accessToken] is invalid", [$e->getMessage()]);
                // 原版 Yggdrasil API 插件在 Access Token 无效时会抛出 UnauthorizedException
                // 所以这里除了写日志以外什么都不做，让下面的代码抛异常
                // 我也不知道为什么是 UnauthorizedException，wiki.vg 和 authlib-injector 文档中都没有记录相关的错误信息
            }
        }

        throw new UnauthorizedException(trans('LittleSkin\\YggdrasilConnect::exceptions.token.bearer'));
    }
}
