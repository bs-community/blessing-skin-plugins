<?php

namespace Yggdrasil\Controllers;

use App\Models\Player;
use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Log;
use Ramsey\Uuid\Uuid;
use Yggdrasil\Exceptions\ForbiddenOperationException;
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Models\Profile;
use Yggdrasil\Models\Token;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        /**
         * 注意，新版账户验证中 username 字段填的是邮箱，
         * 只有旧版的用户填的才是用户名（legacy = true）.
         */
        $identification = strtolower($request->input('username'));
        Log::channel('ygg')->info("User [$identification] is try to authenticate with", [$request->except(['username', 'password'])]);
        $user = $this->checkUserCredentials($request);

        // clientToken 原样返回，如果没提供就给客户端生成一个
        $clientToken = $request->input('clientToken', Uuid::uuid4()->getHex()->toString());
        // clientToken 原样返回，生成新 accessToken 并格式化为不带符号的 UUID
        $accessToken = Uuid::uuid4()->getHex()->toString();

        // 实例化并存储 Token
        $token = new Token($clientToken, $accessToken);
        $token->owner = $identification;

        // 准备响应
        $availableProfiles = $this->getAvailableProfiles($user);

        $result = [
            'accessToken' => $token->accessToken,
            'clientToken' => $token->clientToken,
            'availableProfiles' => $availableProfiles,
        ];

        if ($request->input('requestUser')) {
            // 用户 ID 根据其邮箱生成
            $result['user'] = [
                'id' => Uuid::uuid5($user->email, Uuid::NAMESPACE_DNS)->getHex()->toString(),
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
            'parameters' => json_encode($request->except('username', 'password')),
        ]);

        return json($result);
    }

    public function refresh(Request $request)
    {
        $clientToken = $request->input('clientToken');
        $accessToken = $request->input('accessToken');

        Log::channel('ygg')->info("Try to refresh access token [$accessToken] with client token [$clientToken]");

        $token = Token::find($accessToken);
        if (!$token) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.token.invalid'));
        }

        /** @var User */
        $user = User::where('email', $token->owner)->first();
        if (!$user) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.not-existed'));
        }
        if (!is_null($user->locale)) {
            app()->setLocale($user->locale);
        }

        if ($clientToken && $token->clientToken !== $clientToken) {
            Log::info("Expect client token to be [$token->clientToken]");
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.token.not-matched'));
        }

        Log::channel('ygg')->info("The given access token is owned by user [$token->owner]");

        if ($user->permission == User::BANNED) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.banned'));
        }

        $availableProfiles = $this->getAvailableProfiles($user);

        $result = [
            'accessToken' => $token->accessToken,
            'clientToken' => $token->clientToken, // 原样返回
            'availableProfiles' => $availableProfiles,
        ];

        if ($request->input('requestUser')) {
            $result['user'] = [
                'id' => Uuid::uuid5($user->email, Uuid::NAMESPACE_DNS)->getHex()->toString(),
                'properties' => [],
            ];
        }

        // 当指定了 selectedProfile 时
        if ($selected = $request->get('selectedProfile')) {
            if (!Player::where('name', $selected['name'])->first()) {
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

            if (!isset($result['selectedProfile'])) {
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
        Cache::forget("yggdrasil-token-$accessToken");
        Log::channel('ygg')->info("The old access token [$accessToken] is now revoked");

        $token->accessToken = Uuid::uuid4()->getHex()->toString();
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

        Log::channel('ygg')->info('Check if an access token is valid', compact('clientToken', 'accessToken'));

        $token = Token::find($accessToken);
        if ($token && $token->isValid()) {
            if ($clientToken && $clientToken !== $token->clientToken) {
                throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.token.not-matched'));
            }

            Log::channel('ygg')->info('Given access token is valid and matches the client token');

            /** @var User */
            $user = User::where('email', $token->owner)->first();
            if (!is_null($user->locale)) {
                app()->setLocale($user->locale);
            }

            if ($user->permission == User::BANNED) {
                throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.user.banned'));
            }

            ygg_log([
                'action' => 'validate',
                'user_id' => $user->uid,
                'parameters' => json_encode($request->except('accessToken')),
            ]);

            return response()->noContent();
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
        $tokens = Arr::wrap(Cache::get("yggdrasil-id-$identification"));
        array_walk($tokens, function (Token $token) {
            Cache::forget('yggdrasil-token-'.$token->accessToken);
        });
        Cache::forget("yggdrasil-id-$identification");

        Log::channel('ygg')->info("User [$identification] signed out, all tokens revoked");

        ygg_log([
            'action' => 'signout',
            'user_id' => $user->uid,
        ]);

        return response()->noContent();
    }

    public function invalidate(Request $request)
    {
        $clientToken = $request->input('clientToken');
        $accessToken = $request->input('accessToken');

        Log::channel('ygg')->info('Try to invalidate an access token', compact('clientToken', 'accessToken'));

        // 不用检查 clientToken 与 accessToken 是否匹配
        $token = Cache::get("yggdrasil-token-$accessToken");
        if ($token) {
            $identification = strtolower($token->owner);
            $tokens = Arr::wrap(Cache::get("yggdrasil-id-$identification"));
            $tokens = array_filter($tokens, function (Token $token) use ($accessToken) {
                return $token->accessToken !== $accessToken;
            });
            Cache::put("yggdrasil-id-$identification", $tokens);

            Cache::forget("yggdrasil-token-$accessToken");

            ygg_log([
                'action' => 'invalidate',
                'user_id' => User::where('email', $token->owner)->first()->uid,
                'parameters' => json_encode($request->json()->all()),
            ]);

            Log::channel('ygg')->info("Access token [$accessToken] was successfully revoked");
        } else {
            Log::channel('ygg')->error("Invalid access token [$accessToken], nothing to do");
        }

        // 无论操作是否成功都应该返回 204
        return response()->noContent();
    }

    protected function checkUserCredentials(Request $request, $checkBanned = true)
    {
        $identification = $request->input('username');
        $password = $request->input('password');

        if (is_null($identification) || is_null($password)) {
            throw new IllegalArgumentException(trans('Yggdrasil::exceptions.auth.empty'));
        }

        /** @var User */
        $user = User::where('email', $identification)->first();

        if (!$user) {
            throw new ForbiddenOperationException(trans('Yggdrasil::exceptions.auth.not-existed', compact('identification')));
        }
        if (!is_null($user->locale)) {
            app()->setLocale($user->locale);
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

    protected function storeToken(Token $token, $identification)
    {
        $timeToFullyExpired = option('ygg_token_expire_2');
        Cache::put("yggdrasil-token-{$token->accessToken}", $token, $timeToFullyExpired);

        $limit = (int) option('ygg_tokens_limit', 10);
        $tokens = Arr::wrap(Cache::get("yggdrasil-id-$identification"));
        if (count($tokens) >= $limit) {
            $expired = array_shift($tokens);
            if ($expired) {
                Cache::forget('yggdrasil-token-'.$expired->accessToken);
            }
        }
        $tokens[] = $token;
        Cache::put("yggdrasil-id-$identification", $tokens);

        Log::channel('ygg')->info("Serialized token stored to cache with expiry time $timeToFullyExpired minutes", [
            'keys' => ["yggdrasil-token-{$token->accessToken}"],
            'token' => $token,
        ]);
    }
}
