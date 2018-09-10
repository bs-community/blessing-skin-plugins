<?php

namespace Integration\Authme\Cipher;

use App\Services\Cipher\BaseCipher;

class SALTED2MD5 extends BaseCipher
{
    /**
     * MD5 hash with salt.
     */
    public function hash($value, $salt = '')
    {
        return md5(md5($value).$salt);
    }

    public function generateSalt()
    {
        // Format /^[0-9a-f]{6}$/
        return bin2hex(random_bytes(3));
    }
}
