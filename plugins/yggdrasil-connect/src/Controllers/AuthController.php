<?php

namespace LittleSkin\YggdrasilConnect\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;
use LittleSkin\YggdrasilConnect\Models\AccessToken;
use LittleSkin\YggdrasilConnect\Models\Profile;
use LittleSkin\YggdrasilConnect\Models\User;
use LittleSkin\YggdrasilConnect\Models\UUID;
use Ramsey\Uuid\Uuid as RamseyUuid;

class AuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        $identification = $request->input('username');
        /** @var User */
        $user = auth()->user();
        $uid = $user->uid;

        // clientToken 原样返回，如果没提供就给客户端生成一个
        $clientToken = $request->input('clientToken', RamseyUuid::uuid4()->getHex()->toString());

        $availableProfiles = Profile::getAvailableProfiles($user);

        if (empty($availableProfiles)) {
            Log::channel('ygg')->info("User [$uid] has no available profiles");
            throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.user.no-available-profiles'));
        }

        $resp = [
            'accessToken' => '',
            'clientToken' => $clientToken,
        ];

        if ($request->input('requestUser')) {
            // 用户 ID 根据其 UID 生成
            $resp['user'] = ['id' => User::getUserUuid($user), 'properties' => []];
        }

        if (count($availableProfiles) === 1) {
            // 当用户只有一个角色时自动帮他选择
            $resp['selectedProfile'] = $availableProfiles[0];
            $resp['availableProfiles'] = [$availableProfiles[0]];
        } elseif (!filter_var($identification, FILTER_VALIDATE_EMAIL)) {
            // 如果是角色名登录，就直接绑定角色
            foreach ($availableProfiles as $profile) {
                if (strcasecmp($profile['name'], $identification) === 0) {
                    $resp['selectedProfile'] =  $profile;
                    $resp['availableProfiles'] = [$profile];
                    break;
                }
            }
        } else {
            $resp['availableProfiles'] = $availableProfiles;
        }

        $accessToken = AccessToken::create($user);
        if (!empty($resp['selectedProfile'])) {
            $accessToken = $accessToken->refresh($resp['selectedProfile']['id']);
        }

        $resp['accessToken'] = $accessToken->jwt;
        Log::channel('ygg')->info("New access token [$accessToken->jwt] generated for user [$uid]");

        Log::channel('ygg')->info("User [$uid] authenticated successfully", [compact('availableProfiles')]);

        ygg_log([
            'action' => 'authenticate',
            'user_id' => $user->uid,
            'parameters' => json_encode($request->except('username', 'password')),
        ]);

        return json($resp);
    }

    public function refresh(Request $request): JsonResponse
    {
        $requireProfileInfo = !($request->input('selectedProfile') === null);
        $validation = Validator::make($request->all(), [
            'selectedProfile' => ['nullable'],
            'selectedProfile.id' => [Rule::requiredIf($requireProfileInfo), 'string'],
            'selectedProfile.name' => [Rule::requiredIf($requireProfileInfo), 'string'],
        ]);

        if ($validation->fails()) {
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::exceptions.illegal'));
        }

        $clientToken = $request->input('clientToken', RamseyUuid::uuid4()->getHex()->toString());

        // 中间件里 isRefreshable() 的时候已经确定过了用户是没有注销的
        /** @var User */
        $user = auth()->user();
        $accessTokenToBeRefreshed = $user->yggdrasilToken();

        Log::channel('ygg')->info("Try to refresh access token [$accessTokenToBeRefreshed->jwt] with client token [$clientToken]");
        Log::channel('ygg')->info("The given access token is owned by user [$user->uid]");

        // refresh() 的时候会检查 Access Token 是否已经绑定到角色 & 绑定角色是否一致，所以这里不需要再检查了
        $selectedProfile = $request->input('selectedProfile.id') ?? $accessTokenToBeRefreshed->selectedProfile;

        $newAccessToken = $accessTokenToBeRefreshed->refresh($selectedProfile);
        Log::channel('ygg')->info("New token [$newAccessToken->jwt] generated for user [$user->uid]");

        $accessTokenToBeRefreshed->revoke();
        Log::channel('ygg')->info("The old access token [$accessTokenToBeRefreshed->jwt] is now revoked");

        Log::channel('ygg')->info("Access token refreshed [$accessTokenToBeRefreshed->jwt] => [$newAccessToken->jwt]");

        $profile = [
            'id' => $selectedProfile,
            'name' => UUID::where('uuid', $selectedProfile)->first()->player->name,
        ];

        ygg_log([
            'action' => 'refresh',
            'user_id' => $user->uid,
            'parameters' => json_encode($request->except('accessToken')),
        ]);

        $resp = [
            'accessToken' => $newAccessToken->jwt,
            'clientToken' => $clientToken,
            'selectedProfile' => $profile,
            'availableProfiles' => [$profile],
        ];

        if ($request->input('requestUser')) {
            // 用户 ID 根据其 UID 生成
            $resp['user'] = ['id' => User::getUserUuid($user), 'properties' => []];
        }

        return json($resp);
    }

    public function validate(Request $request): Response|JsonResponse
    {
        ygg_log([
            'action' => 'validate',
            'user_id' => auth()->user()->uid,
            'parameters' => json_encode($request->except('accessToken')),
        ]);

        return response()->noContent();
    }

    public function signout(Request $request): Response
    {
        $user = $request->user();
        Log::channel('ygg')->info("User [$user->uid] is try to signout");

        // 吊销所有令牌
        AccessToken::revokeAllForUser($user);

        Log::channel('ygg')->info("User [$user->uid] signed out, all tokens revoked");

        ygg_log([
            'action' => 'signout',
            'user_id' => $user->uid,
        ]);

        return response()->noContent();
    }

    public function invalidate(Request $request): Response
    {
        /** @var User */
        $user = auth()->user();
        $token = $user->yggdrasilToken();
        $jwt = $token->jwt;
        Log::channel('ygg')->info('Try to invalidate an access token', [$jwt]);

        $token->revoke();

        ygg_log([
            'action' => 'invalidate',
            'user_id' => $token->owner->uid,
            'parameters' => json_encode($request->json()->all()),
        ]);

        Log::channel('ygg')->info("Access token [$jwt] was successfully revoked");

        return response()->noContent();
    }
}
