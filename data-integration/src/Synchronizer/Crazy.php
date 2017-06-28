<?php
/**
 * @Author: printempw
 * @Date:   2016-10-29 21:46:46
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 11:57:21
 */

namespace DataIntegration\Synchronizer;

use DataIntegration\Log;
use DataIntegration\Utils;

class Crazy extends LoginSystemSynchronizer
{
    public function encryptPassword($raw_passwd, $user)
    {
        $username = $user->username;

        $result = app('cipher')->hash($raw_passwd, $username);

        Log::info("[DataIntegration][$username] Password hashed with salt(username): [$username], Hash: [$result], Expecting: [$user->password]");

        return $result;
    }

}
