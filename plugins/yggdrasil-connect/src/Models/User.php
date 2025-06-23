<?php

namespace LittleSkin\YggdrasilConnect\Models;

use App\Models\User as BaseUser;
use Ramsey\Uuid\Uuid as RamseyUuid;

class User extends BaseUser
{
    protected AccessToken $yggdrasilToken;

    public function yggdrasilToken(): AccessToken
    {
        if (!isset($this->yggdrasilToken)) {
            throw new \RuntimeException('Yggdrasil token is not set.');
        }

        return $this->yggdrasilToken;
    }

    public function withYggdrasilToken(AccessToken $token): User
    {
        $this->yggdrasilToken = $token;

        return $this;
    }

    public static function getUserUuid(BaseUser $user): string
    {
        return RamseyUuid::uuid5(RamseyUuid::NAMESPACE_DNS, $user->uid)->getHex()->toString();
    }
}
