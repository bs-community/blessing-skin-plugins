<?php

namespace Yggdrasil\Controllers;

use Log;
use Cache;
use App\Models\User;
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
            throw new IllegalArgumentException('Credentials is null');
        }

        // $token = $ygg->authenticate($username, $password, $clientToken);

        $user = app('users')->get($identification, 'email');

        if (! $user) {
            throw new NotFoundException('No such user');
        }

        if (! $user->verifyPassword($password)) {
            throw new ForbiddenOperationException('Invalid credentials. Invalid username or password.');
        }

        if (! $clientToken) {
            $clientToken = UUID::generate()->string;
        }

        // Remove dashes
        $clientToken = UUID::format($clientToken);
        $accessToken = UUID::generate()->clearDashes();

        $token = new Token($clientToken, $accessToken);
        $token->setOwner($identification);

        Log::info('New token generated and stored', [$token->serialize()]);

        Cache::put("I$identification", serialize($token), YGG_TOKEN_EXPIRE / 60);
        Cache::put("C$clientToken", serialize($token), YGG_TOKEN_EXPIRE / 60);

        return $this->createAuthenticationResponse($token, $user);
    }

    public function refresh(Request $request)
    {
        $clientToken = UUID::format($request->get('clientToken'));
        $accessToken = UUID::format($request->get('accessToken'));

        if ($cache = Cache::get("C$clientToken")) {
            $token = unserialize($cache);
        } else {
            throw new ForbiddenOperationException('Invalid client token');
        }

        Log::info("Try to refresh with access token [$accessToken], expected [".$token->getAccessToken()."]");

        if ($accessToken === $token->getAccessToken()) {
            // Generate new access token
            $token->setAccessToken(UUID::generate()->clearDashes());

            $identification = $token->getOwner();

            Cache::put("I$identification", serialize($token), YGG_TOKEN_EXPIRE / 60);
            Cache::put("C$clientToken", serialize($token), YGG_TOKEN_EXPIRE / 60);

            return $this->createAuthenticationResponse($token, $user);
        } else {
            throw new ForbiddenOperationException('Invalid access token');
        }
    }

    protected function createAuthenticationResponse(Token $token, User $user)
    {
        $availableProfiles = [];

        foreach ($user->players()->get() as $player) {
            $uuid = Profile::getUuidFromName($player->player_name);

            $availableProfiles[] = [
                'id' => $uuid,
                'name' => $player->player_name
            ];
        }

        $result = [
            'accessToken' => UUID::import($token->getAccessToken())->string,
            'clientToken' => UUID::import($token->getClientToken())->string,
            'availableProfiles' => $availableProfiles
        ];

        if (! empty($availableProfiles) && count($availableProfiles) == 1) {

            $result['selectedProfile'] = $availableProfiles[0];

            if (app('request')->get('requestUser')) {
                $result['user'] = [
                    'id' => $result['selectedProfile']['id'],
                    'properties' => []
                ];
            }
        }

        return json($result);
    }

    public function validate(Request $request)
    {
        $clientToken = UUID::format($request->get('clientToken'));
        $accessToken = UUID::format($request->get('accessToken'));

        if ($cache = Cache::get("C$clientToken")) {
            $token = unserialize($cache);

            if ($accessToken === $token->getAccessToken()) {
                return response('')->setStatusCode(204);
            }
        }

        throw new ForbiddenOperationException('Invalid client token');
    }

    public function signout(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        $user = app('users')->get($username, 'username');

        if (! $user) {
            throw new NotFoundException('No such user');
        }

        if ($user->verifyPassword($password)) {
            $uuid = Profile::getUuidFromName($username);

            if ($cache = Cache::get("U$uuid")) {
                $clientToken = unserialize($cache)->getClientToken();

                Cache::forget("U$uuid");
                Cache::forget("C$clientToken");

                return response('');
            }
        } else {
            throw new ForbiddenOperationException('Invalid credentials. Invalid username or password.');
        }
    }

    public function invalidate(Request $request)
    {
        $clientToken = UUID::format($request->get('clientToken'));
        $accessToken = UUID::format($request->get('accessToken'));

        if ($cache = Cache::get("C$clientToken")) {
            $token = unserialize($cache);
            $uuid = $token->getOwner();

            if ($accessToken === $token->getAccessToken()) {
                Cache::forget("U$uuid");
                Cache::forget("C$clientToken");

                return response('');
            } else {
                throw new ForbiddenOperationException('Invalid access token');
            }
        } else {
            throw new ForbiddenOperationException('Invalid client token');
        }

    }

}
