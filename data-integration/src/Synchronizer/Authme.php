<?php

namespace DataIntegration\Synchronizer;

use DB;
use Utils;
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
        $tableName = unserialize(option('da_connection'))['table'];

        $newRecord = [
            $this->columns['username'] => $username,
            $this->columns['password'] => $result->password,
            $this->columns['ip']       => $result->ip
        ];

        if (app('db.target')->fetchArray("SHOW COLUMNS FROM `$tableName` LIKE 'realname'")) {
            $newRecord['realname'] = $username;
        }

        app('db.target')->insert($newRecord);

        Log::info("[DataIntegration][$username] Add a new user to Authme database.");
    }

}
