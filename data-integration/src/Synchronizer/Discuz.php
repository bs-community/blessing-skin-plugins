<?php
/**
 * @Author: printempw
 * @Date:   2016-10-29 21:46:46
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 11:57:21
 */

namespace DataIntegration\Synchronizer;

use DataIntegration\Log;
use Illuminate\Support\Arr;

class Discuz extends ForumSynchronizer
{
    public function encryptPassword($raw_passwd, $user)
    {
        $salt = Arr::get(app('db.target')->select('email', $user->email), 'salt', config('secure.salt'));

        $result = app('cipher')->hash($raw_passwd, $salt);

        Log::info("[DataIntegration][$user->username] Password hashed with salt: [$salt], Hash: [$result], Expecting: [$user->password]");

        return $result;
    }

}
