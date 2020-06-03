<?php

namespace Yggdrasil\Controllers;

use Log;
use Cache;
use App\Models\User;
use App\Models\Player;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Models\Token;
use Illuminate\Http\Request;
use Yggdrasil\Models\Profile;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        /**
         * 注意，新版账户验证中 username 字段填的是邮箱，
         * 只有旧版的用户填的才是用户名（legacy = true）
         */
        $identification = strtolower($request->input('username'));
        Log::channel('ygg')->info("User [$identification] is try to authenticate with", [$request->except(['username', 'password'])]);
        $user = $this->checkUserCredentials($request);

        // clientToken 原样返回，如果没提供就给客户端生成一个
        $clientToken = $request->input('clientToken', UUID::generate()->clearDashes());
        // clientToken 原样返回，生成新 accessToken 并格式化为不带符号的 UUID
        $accessToken = UUID::generate()->clearDashes();

        // 吊销该用户的其他令牌
        if ($cache = Cache::get("ID_$identification")) {
            $expiredAccessToken = unserialize($cache)->accessToken;

            Cache::forget("ID_$identification");
            Cache::forget("TOKEN_$expiredAccessToken");
        }

        // 实例化并存储 Token
        $token = new Token($clientToken, $accessToken);
        $token->owner = $identification;

        // 准备响应
        $availableProfiles = $this->getAvailableProfiles($user);

        $result = [
            'accessToken' => $token->accessToken,
            'clientToken' => $token->clientToken,
            'availableProfiles' => $availableProfiles
        ];

        if ($request->input('requestUser')) {
            // 用户 ID 根据其邮箱生成
            $result['user'] = [
                'id' => UUID::generate(5, $user->email, UUID::NS_DNS)->clearDashes(),
                'properties' => [],
            ];
        }

        // 当用户只有一个角色时自动帮他选择
        if (!empty($availableProfiles) && count($availableProfiles) === 1) {
            $result['selectedProfile'] = $availableProfiles[0];
            $token->profileId = $availableProfiles[0]['id'];
        }

        $this->storeToken($token, $identification);
        Log::channel('ygg')->info("New access token [$accessToken] generated for user [$identification]");

        Log::channel('ygg')->info("User [$identification] authenticated successfully", [compact('availableProfiles')]);

        ygg_log([
            'action' => 'authenticate',
            'user_id' => $user->uid,
            'parameters' => json_encode($request->except('username', 'password'))
        ]);

        return json($result);
    }

    public function refresh(Request $request)
    {
        $clientToken = $request->input('clientToken');
        $accessToken = $request->input('accessToken');

        Log::channel('ygg')->info("Try to refresh access token [$accessToken] with client token [$clientToken]");

        $token = Token::lookup($accessToken);
        if (! $token) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.token.invalid'));
        }

        if ($clientToken && $token->clientToken !== $clientToken) {
            Log::info("Expect client token to be [$token->clientToken]");
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.token.not-matched'));
        }

        $user = User::where('email', $token->owner)->first();

        if (!$user) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.not-existed'));
        }

        Log::channel('ygg')->info("The given access token is owned by user [$token->owner]");

        if ($user->permission == User::BANNED) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.banned'));
        }

        $availableProfiles = $this->getAvailableProfiles($user);

        $result = [
            'accessToken' => $token->accessToken,
            'clientToken' => $token->clientToken, // 原样返回
            'availableProfiles' => $availableProfiles
        ];

        if ($request->input('requestUser')) {
            $result['user'] = [
                'id' => UUID::generate(5, $user->email, UUID::NS_DNS)->clearDashes(),
                'properties' => [],
            ];
        }

        // 当指定了 selectedProfile 时
        if ($selected = $request->get('selectedProfile')) {
            if (! Player::where('name', $selected['name'])->first()) {
                throw new IllegalArgumentException(trans('Yggdrasil::exceptions.player.not-existed'));
            }

            if ($token->profileId != '' && $selected != $token->profileId) {
                throw new IllegalArgumentException(trans('Yggdrasil::exceptions.player.not-matched'));
            }

            foreach ($availableProfiles as $profile) {
                if ($profile['id'] == $selected['id']) {
                    $result['selectedProfile'] = $profile;
                }
            }

            if (! isset($result['selectedProfile'])) {
                throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.player.owner'));
            }

            $token->profileId = $result['selectedProfile']['id'];
        } else {
            foreach ($availableProfiles as $profile) {
                if ($profile['id'] == $token->profileId) {
                    $result['selectedProfile'] = $profile;
                }
            }
        }

        // 上面那一大票检测完了，最后再刷新令牌
        Cache::forget("TOKEN_$accessToken");
        Log::channel('ygg')->info("The old access token [$accessToken] is now revoked");

        $token->accessToken = UUID::generate()->clearDashes();
        $token->createdAt = time();
        Log::channel('ygg')->info("New token [$token->accessToken] generated for user [$user->email]");
        $this->storeToken($token, $token->owner);

        Log::channel('ygg')->info("Access token refreshed [$accessToken] => [$token->accessToken]");

        ygg_log([
            'action' => 'refresh',
            'user_id' => $user->uid,
            'parameters' => json_encode($request->except('accessToken')),
        ]);

        $result['accessToken'] = $token->accessToken;
        return json($result);
    }

    public function validate(Request $request)
    {
        $clientToken = $request->input('clientToken');
        $accessToken = $request->input('accessToken');

        Log::channel('ygg')->info("Check if an access token is valid", compact('clientToken', 'accessToken'));

        $token = Token::lookup($accessToken);
        if ($token && $token->isValid()) {

            if ($clientToken && $clientToken !== $token->clientToken) {
                throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.token.not-matched'));
            }

            Log::channel('ygg')->info('Given access token is valid and matches the client token');

            $user = User::where('email', $token->owner)->first();

            if ($user->permission == User::BANNED) {
                throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.banned'));
            }

            ygg_log([
                'action' => 'validate',
                'user_id' => $user->uid,
                'parameters' => json_encode($request->except('accessToken')),
            ]);

            return response('')->setStatusCode(204);
        } else {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.token.invalid'));
        }
    }

    public function signout(Request $request)
    {
        $identification = strtolower($request->input('username'));
        Log::channel('ygg')->info("User [$identification] is try to signout");
        $user = $this->checkUserCredentials($request, false);

        // 吊销所有令牌
        if ($cache = Cache::get("ID_$identification")) {
            $accessToken = unserialize($cache)->accessToken;

            Cache::forget("ID_$identification");
            Cache::forget("TOKEN_$accessToken");
        }

        Log::channel('ygg')->info("User [$identification] signed out, all tokens revoked");

        ygg_log([
            'action' => 'signout',
            'user_id' => $user->uid,
        ]);

        return response('')->setStatusCode(204);
    }

    public function invalidate(Request $request)
    {
        $clientToken = $request->input('clientToken');
        $accessToken = $request->input('accessToken');

        Log::channel('ygg')->info("Try to invalidate an access token", compact('clientToken', 'accessToken'));

        // 据说不用检查 clientToken 与 accessToken 是否匹配
        if ($cache = Cache::get("TOKEN_$accessToken")) {
            $token = unserialize($cache);
            $identification = strtolower($token->owner);

            Cache::forget("ID_$identification");
            Cache::forget("TOKEN_$accessToken");

            ygg_log([
                'action' => 'invalidate',
                'user_id' => User::where('email', $token->owner)->first()->uid,
                'parameters' => json_encode($request->json()->all()),
            ]);

            Log::channel('ygg')->info("Access token [$accessToken] was successfully revoked");
        } else {
            Log::channel('ygg')->error("Invalid access token [$accessToken], nothing to do");
        }

        // 据说无论操作是否成功都应该返回 204
        return response('')->setStatusCode(204);
    }

    protected function checkUserCredentials(Request $request, $checkBanned = true)
    {
        $identification = $request->input('username');
        $password = $request->input('password');

        if (is_null($identification) || is_null($password)) {
            throw new IllegalArgumentException(trans('Yggdrasil::exceptions.auth.empty'));
        }

        $user = User::where('email', $identification)->first();

        if (!$user) {
            throw new ForbiddenOperationException(
                trans('Yggdrasil::exceptions.auth.not-existed', compact('identification'))
            );
        }

        if (!$user->verifyPassword($password)) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.auth.not-matched'));
        }

        if ($checkBanned && $user->permission == User::BANNED) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.banned'));
        }

        if (option('require_verification') && $user->verified === false) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.not-verified'));
        }

        return $user;
    }

    protected function getAvailableProfiles(User $user)
    {
        $profiles = [];

        foreach ($user->players as $player) {
            $uuid = Profile::getUuidFromName($player->name);

            $profiles[] = [
                'id' => $uuid,
                'name' => $player->name,
            ];
        }

        return $profiles;
    }

    // 推荐使用 Redis 作为缓存驱动
    protected function storeToken(Token $token, $identification)
    {
        $timeToFullyExpired = option('ygg_token_expire_2');
        // 使用 accessToken 作为缓存主键
        Cache::put("TOKEN_{$token->accessToken}", serialize($token), $timeToFullyExpired);
        // TODO: 实现一个用户可以签发多个 Token
        Cache::put("ID_$identification", serialize($token), $timeToFullyExpired);

        Log::channel('ygg')->info("Serialized token stored to cache with expiry time $timeToFullyExpired minutes", [
            'keys' => ["TOKEN_{$token->accessToken}", "ID_$identification"],
            'token' => $token,
        ]);
    }
}
