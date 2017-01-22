<?php
/**
 * @Author: printempw
 * @Date:   2016-10-29 21:46:46
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 11:57:21
 */

namespace DataIntegration\Synchronizer;

use DB;
use Utils;
use Database;
use DataIntegration\Log;

class Authme extends LoginSystemSynchronizer
{
    public function encryptPassword($raw_passwd, $user)
    {
        $salt = $this->getPwdInfo($user->password)['salt'];

        if ($salt != "") {
            $result = '$SHA$'.$salt.'$'. app('cipher')->hash($raw_passwd, $salt);
        } else {
            $result = app('cipher')->hash($raw_passwd, $salt);
        }

        Log::info("[DataIntegration][$user->username] Password hashed with salt: [$salt], Hash: [$result], Expecting: [$user->password]");

        return $result;
    }

    /**
     * Parse fucking inline salt
     *
     * @see    https://github.com/Xephi/AuthMeReloaded/blob/master/samples/website_integration/sha256/integration.php
     * @param  string $password
     * @return array
     */
    private function getPwdInfo($password)
    {
        $parts = explode('$', $password);

        // if the password is not formatted as $SHA$SALT$PASSWD
        if (!isset($parts[3])) {
            return [
                'password' => $password,
                'salt'     => ''
            ];
        }

        return [
            'password' => $parts[3],
            'salt'     => $parts[2]
        ];
    }

    public function syncFromSelf($username)
    {
        $result = app('users')->get($username, 'username');

        app('db.target')->insert([
            $this->columns['username'] => $username,
            // realname field for Authme
            'realname' => $username,
            $this->columns['password'] => $result->password,
            $this->columns['ip']       => $result->ip
        ]);

        Log::info("[DataIntegration][$username] Add a new user to Authme database.");
    }

}
