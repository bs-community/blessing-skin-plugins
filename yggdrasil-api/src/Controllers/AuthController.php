<?php

namespace Yggdrasil\Controllers;

use Cache;
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
    public function authenticate(Request $request, Yggdrasil $ygg)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $clientToken = $request->get('clientToken');

        if (is_null($username) || is_null($password)) {
            throw new IllegalArgumentException('Credentials is null');
        }

        $token = $ygg->authenticate($username, $password, $clientToken);

        $uuid = $token->getOwnerUuid();

        $selectedProfile = [
            'id' => $uuid,
            'name' => $username
        ];

        return json([
            'accessToken' => $token->getAccessToken(),
            'clientToken' => $token->getClientToken(),
            'availableProfiles' => [$selectedProfile],
            'selectedProfile' => $selectedProfile,
            'user' => [
                'id' => $uuid,
                'properties' => []
            ]
        ]);
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

        if ($accessToken === $token->getAccessToken()) {
            // Generate new access token
            $token->setAccessToken(UUID::generate()->clearDashes());

            $uuid = $token->getOwnerUuid();

            Cache::put("U$uuid", serialize($token), YGG_TOKEN_EXPIRE / 60);
            Cache::put("C$clientToken", serialize($token), YGG_TOKEN_EXPIRE / 60);

            $result = [
                'accessToken' => $token->getAccessToken(),
                'clientToken' => $token->getClientToken(),
                'selectedProfile' => [
                    'id' => $uuid,
                    'name' => Profile::createFromUuid($uuid)->getName()
                ]
            ];

            if ($request->get('requestUser')) {
                $result['user'] = [
                    'id' => $uuid,
                    'properties' => []
                ];
            }

            return json($result);
        } else {
            throw new ForbiddenOperationException('Invalid access token');
        }
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

        throw new ForbiddenOperationException('Invalid token');
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
            $uuid = $token->getOwnerUuid();

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
