<?php

namespace DataIntegration\Synchronizer;

use DataIntegration\Log;
use Illuminate\Support\Arr;

class BeeLogin extends LoginSystemSynchronizer
{
    public function encryptPassword($raw_passwd, $user)
    {
        $salt = Arr::get(app('db.target')->select('username', $user->username), 'salt', config('secure.salt'));

        $result = app('cipher')->hash($raw_passwd, $salt);

        Log::info("[DataIntegration][$user->username] Password hashed with salt: [$salt], Hash: [$result], Expecting: [$user->password]");

        return $result;
    }

}
