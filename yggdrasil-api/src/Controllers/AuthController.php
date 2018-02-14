<?php

namespace Yggdrasil\Controllers;

use Cache;
use App\Models\User;
use App\Models\Player;
use Yggdrasil\Utils\Log;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Models\Token;
use Illuminate\Http\Request;
use Yggdrasil\Models\Profile;
use Illuminate\Routing\Controller;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\IllegalArgumentException;
use Yggdrasil\Exceptions\ForbiddenOperationException;
use Yggdrasil\Service\YggdrasilServiceInterface as Yggdrasil;

class AuthController extends Controller
{
    public function __construct(Request $request)
    {
        Log::info('Recieved request', [$request->path(), $request->json()->all()]);
    }

    public function hello(Request $request)
    {
        // Default skin domain whitelist:
        // - Specified by option 'site_url'
        // - Extract host from current URL
        $extra = option('ygg_skin_domain') === '' ? [] : explode(',', option('ygg_skin_domain'));
        $skinDomains = array_map('trim', array_unique(array_merge($extra, [
            parse_url(option('site_url'), PHP_URL_HOST),
            $request->getHost()
        ])));

        $privateKey = openssl_pkey_get_private(option('ygg_private_key'));

        if (! $privateKey) {
            throw new IllegalArgumentException('无效的 RSA 私钥，请访问插件配置页重新设置');
        }

        return json([
            'meta' => [
                'serverName' => option('site_name'),
                'implementationName' => 'Yggdrasil API for Blessing Skin',
                'implementationVersion' => plugin('yggdrasil-api')['version']
            ],
            'skinDomains' => $skinDomains,
            'signaturePublickey' => openssl_pkey_get_details($privateKey)['key']
        ]);
    }

    public function authenticate(Request $request, Yggdrasil $ygg)
    {
        /**
         * 注意，新版账户验证中 username 字段填的是邮箱，
         * 只有旧版的用户填的才是用户名（legacy = true）
         */
        $identification = $request->get('username');
        $password = $request->get('password');
        $clientToken = $request->get('clientToken');

        if (is_null($identification) || is_null($password)) {
            throw new IllegalArgumentException('邮箱或者密码没填哦');
        }

        $user = app('users')->get($identification, 'email');

        if (! $user) {
            throw new ForbiddenOperationException('用户不存在');
        }

        if (! $user->verifyPassword($password)) {
            throw new ForbiddenOperationException('输入的邮箱与密码不匹配');
        }

        if ($user->getPermission() == User::BANNED) {
            throw new ForbiddenOperationException('你已经被本站封禁，详情请询问管理人员');
        }

        $token = $ygg->authenticate($identification, $password, $clientToken);

        $availableProfiles = $this->getAvailableProfiles($user);

        $result = [
            'accessToken' => UUID::format($token->accessToken),
            'clientToken' => $token->clientToken, // clientToken 原样返回
            'availableProfiles' => $availableProfiles
        ];

        if (app('request')->get('requestUser')) {
            $result['user'] = [
                'id' => UUID::generate(5, $user->email, UUID::NS_DNS)->clearDashes(),
                'properties' => []
            ];
        }

        if (!empty($availableProfiles) && count($availableProfiles) == 1) {
            $result['selectedProfile'] = $availableProfiles[0];
        }

        return json($result);
    }

    public function refresh(Request $request, Yggdrasil $ygg)
    {
        // clientToken 原样返回
        $clientToken = $request->get('clientToken');
        $accessToken = UUID::format($request->get('accessToken'));

        // 先不刷新，拿到旧的 Token 实例先
        $token = $ygg->retrieveToken($accessToken);
        $user = app('users')->get($token->owner, 'email');

        if (! $user) {
            throw new ForbiddenOperationException('令牌绑定的用户不存在');
        }

        $availableProfiles = $this->getAvailableProfiles($user);

        $result = [
            'accessToken' => UUID::format($token->accessToken),
            'clientToken' => $token->clientToken, // clientToken 原样返回
            'availableProfiles' => $availableProfiles
        ];

        if (app('request')->get('requestUser')) {
            $result['user'] = [
                'id' => UUID::generate(5, $user->email, UUID::NS_DNS)->clearDashes(),
                'properties' => []
            ];
        }

        // 当指定了 selectedProfile 时
        if ($selected = $request->get('selectedProfile')) {
            if (! Player::where('player_name', $selected['name'])->first()) {
                throw new IllegalArgumentException('请求的角色不存在');
            }

            foreach ($availableProfiles as $profile) {
                if ($profile['id'] == $selected['id']) {
                    $result['selectedProfile'] = $profile;
                }
            }

            if (! isset($result['selectedProfile'])) {
                throw new ForbiddenOperationException('请求的角色不是你的');
            }
        } else {
            if (!empty($availableProfiles) && count($availableProfiles) == 1) {
                $result['selectedProfile'] = $availableProfiles[0];
            }
        }

        // 上面那一大票检测完了，最后再刷新令牌
        $token = $ygg->refresh($clientToken, $accessToken);
        $result['accessToken'] = UUID::format($token->accessToken);

        return json($result);
    }

    protected function getAvailableProfiles(User $user)
    {
        $profiles = [];

        foreach ($user->players()->get() as $player) {
            $uuid = Profile::getUuidFromName($player->player_name);

            $profiles[] = [
                'id' => $uuid,
                'name' => $player->player_name
            ];
        }

        return $profiles;
    }

    public function validate(Request $request, Yggdrasil $ygg)
    {
        $clientToken = UUID::format($request->get('clientToken'));
        $accessToken = UUID::format($request->get('accessToken'));

        if ($ygg->validate($clientToken, $accessToken)) {
            return response('')->setStatusCode(204);
        } else {
            throw new ForbiddenOperationException('提供的 ClientToken 与 AccessToken 不匹配');
        }
    }

    public function signout(Request $request, Yggdrasil $ygg)
    {
        $identification = $request->get('username');
        $password = $request->get('password');

        if (is_null($identification) || is_null($password)) {
            throw new IllegalArgumentException('邮箱或者密码没填哦');
        }

        $ygg->signout($identification, $password);

        return response('')->setStatusCode(204);
    }

    public function invalidate(Request $request, Yggdrasil $ygg)
    {
        $clientToken = UUID::format($request->get('clientToken'));
        $accessToken = UUID::format($request->get('accessToken'));

        // 据说不用检查 clientToken 与 accessToken 是否匹配
        $ygg->invalidate($accessToken);

        return response('')->setStatusCode(204);
    }

}
