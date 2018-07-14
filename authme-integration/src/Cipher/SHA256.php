<?php

namespace Integration\Authme\Cipher;

use App\Services\Cipher\BaseCipher;

/**
 * Authme 神秘的 SHA256 算法。
 *
 * @see https://github.com/AuthMe/AuthMeReloaded/blob/master/samples/website_integration/Sha256.php
 */
class SHA256 extends BaseCipher
{
    public function hash($value, $salt = '')
    {
        $salt = (strlen($salt) == 16) ? $salt : $this->generateSalt();

        return '$SHA$'.$salt.'$'.hash('sha256', hash('sha256', $value).$salt);
    }

    public function verify($password, $hash, $salt = '')
    {
        // Parse AuthMe's fucking in-line salt from hash
        $salt = $this->parseHash($hash)['salt'];

        return hash_equals($hash, $this->hash($password, $salt));
    }

    protected function parseHash($hash)
    {
        $parts = explode('$', $hash);

        // Hash formatted as $SHA$salt$hash
        return [
            'hash' => $parts[3],
            'salt' => $parts[2]
        ];
    }

    protected function generateSalt() {
        // Format /^[0-9a-f]{16}$/
        return bin2hex(random_bytes(8));
    }
}
