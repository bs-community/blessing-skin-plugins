<?php

namespace Yggdrasil\Service;

use DB;
use Cache;
use App\Models\User;
use App\Models\Player;
use Yggdrasil\Utils\Log;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Models\Token;
use Yggdrasil\Models\Profile;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class BlessingYggdrasilService implements YggdrasilServiceInterface
{
    public function authenticate($identification, $password, $clientToken)
    {
        if (! $clientToken) {
            $clientToken = UUID::generate()->clearDashes();
        }

        // clientToken 原样返回，accessToken 格式化为不带符号的 UUID
        $accessToken = UUID::generate()->clearDashes();

        $token = new Token($clientToken, $accessToken);
        $token->owner = $identification;

        // 吊销其他令牌
        if ($cache = Cache::get("ID_$identification")) {
            $accessToken = unserialize($cache)->accessToken;

            Cache::forget("ID_$identification");
            Cache::forget("TOKEN_$accessToken");
        }

        $this->storeToken($token, $identification);

        Log::info('New token generated and stored', [$token->serialize()]);

        return $token;
    }

    public function refresh($clientToken, $accessToken)
    {
        if ($cache = Cache::get("TOKEN_$accessToken")) {
            $token = unserialize($cache);

            Log::info("Try to refresh with client token [$clientToken], expected [{$token->clientToken}]");

            if ($clientToken && $token->clientToken !== $clientToken) {
                throw new ForbiddenOperationException('提供的 ClientToken 与 AccessToken 不匹配');
            }
        } else {
            // 这里不需要检测令牌是否暂时失效
            // 因为如果令牌完全失效就会被直接清除出缓存
            throw new ForbiddenOperationException('无效的 AccessToken，请重新登录');
        }

        // 签发并储存新的 AccessToken
        Cache::forget("TOKEN_$accessToken");
        $token->accessToken = UUID::generate()->clearDashes();
        $this->storeToken($token, $token->owner);

        return $token;
    }

    public function validate($clientToken, $accessToken)
    {
        if ($cache = Cache::get("TOKEN_$accessToken")) {
            $token = unserialize($cache);

            if ($clientToken && $clientToken !== $token->clientToken) {
                return false;
            }

            // 未提供 clientToken 且 accessToken 有效时
            return true;
        }

        return false;
    }

    public function signout($identification, $password)
    {
        $user = app('users')->get($identification, 'email');

        if (! $user) {
            throw new ForbiddenOperationException('用户不存在');
        }

        if ($user->verifyPassword($password)) {
            $uuid = Profile::getUuidFromName($identification);

            if ($cache = Cache::get("ID_$identification")) {
                $accessToken = unserialize($cache)->accessToken;

                Cache::forget("ID_$identification");
                Cache::forget("TOKEN_$accessToken");
            }
        } else {
            throw new ForbiddenOperationException('输入的邮箱与密码不匹配');
        }
    }

    public function invalidate($accessToken)
    {
        if ($cache = Cache::get("TOKEN_$accessToken")) {
            $token = unserialize($cache);
            $identification = $token->owner;

            Cache::forget("ID_$identification");
            Cache::forget("TOKEN_$accessToken");
        }
    }

    // 推荐使用 Redis 作为缓存驱动
    protected function storeToken(Token $token, $identification)
    {
        $timeToFullyExpired = option('ygg_token_expire_2') / 60;
        // 使用 accessToken 作为缓存主键
        Cache::put("TOKEN_{$token->accessToken}", serialize($token), $timeToFullyExpired);
        // TODO: 实现一个用户可以签发多个 Token
        Cache::put("ID_$identification", serialize($token), $timeToFullyExpired);
    }

    public function retrieveToken($accessToken)
    {
        if ($cache = Cache::get("TOKEN_$accessToken")) {
            return unserialize($cache);
        } else {
            // 直接抛异常算了
            throw new ForbiddenOperationException('无效的 AccessToken，请重新登录');
        }
    }

    public function joinServer($accessToken, $selectedProfile, $serverId)
    {
        $result = DB::table('uuid')->where('uuid', $selectedProfile)->first();

        if (! $result) {
            throw new IllegalArgumentException('无效的 UUID');
        }

        $player = Player::where('player_name', $result->name)->first();

        if (! $player) {
            throw new IllegalArgumentException('角色不存在');
        }

        $identification = $player->user()->first()->email;

        if ($cache = Cache::get("ID_$identification")) {

            $token = unserialize($cache);

            if ($token->accessToken != $accessToken) {
                throw new IllegalArgumentException('无效的 AccessToken，请重新登录');
            }

            Cache::forever("SERVER_$serverId", $selectedProfile);
        } else {
            throw new IllegalArgumentException('未查找到有效的登录信息，请重新登录');
        }
    }

    public function hasJoinedServer($name, $serverId, $ip)
    {
        if ($selectedProfile = Cache::get("SERVER_$serverId")) {
            $profile = Profile::createFromUuid($selectedProfile);

            // TODO: 检查 IP 地址
            if ($name == $profile->name) {
                Cache::forget("SERVER_$serverId");

                return $profile;
            }
        }
    }
}
