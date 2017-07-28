<?php

namespace Yggdrasil\Service;

use Cache;
use App\Models\User;
use Yggdrasil\Utils\UUID;
use Yggdrasil\Models\Token;
use Yggdrasil\Models\Profile;
use Yggdrasil\Exceptions\NotFoundException;
use Yggdrasil\Exceptions\ForbiddenOperationException;

class BlessingYggdrasilService implements YggdrasilServiceInterface
{

    public function __construct()
    {
        # code...
    }

    public function authenticate($username, $password, $clientToken)
    {
        $user = app('users')->get($username, 'username');

        if (! $user) {
            throw new NotFoundException('No such user');
        }

        if ($user->verifyPassword($password)) {
            if (! $clientToken) {
                $clientToken = UUID::generate()->string;
            }

            // Remove dashes
            $clientToken = UUID::format($clientToken);

            $uuid = Profile::getUuidFromName($username);

            $accessToken = UUID::generate()->clearDashes();

            $token = new Token($clientToken, $accessToken);
            $token->setOwnerUuid($uuid);

            Cache::put("U$uuid", serialize($token), YGG_TOKEN_EXPIRE / 60);
            Cache::put("C$clientToken", serialize($token), YGG_TOKEN_EXPIRE / 60);

            return $token;
        } else {
            throw new ForbiddenOperationException('Invalid credentials. Invalid username or password.');
        }
    }
}
