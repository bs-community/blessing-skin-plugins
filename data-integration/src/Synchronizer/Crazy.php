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

        $text = "ÜÄaeut//&/=I " . $raw_passwd . "7421€547" . $username . "__+IÄIH§%NK " . $raw_passwd;
        $t1 = unpack("H*", $text);
        $t2 = substr($t1[1], 0, mb_strlen($text, 'UTF-8')*2);
        $t3 = pack("H*", $t2);

        $result = app('cipher')->hash($t3);

        Log::info("[DataIntegration][$username] Password hashed with username: [$username], Hash: [$result], Expecting: [$user->password]");

        return $result;
    }

}
