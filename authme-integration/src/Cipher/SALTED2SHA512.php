<?php

namespace Integration\Authme\Cipher;

use App\Services\Cipher\BaseCipher;

class SALTED2SHA512 extends BaseCipher
{
    /**
     * SHA512 hash with salt
     */
    public function hash($value, $salt = '')
    {
        return hash('sha512', hash('sha512', $value).$salt);
    }

    public function generateSalt() {
        // Format /^[0-9a-f]{6}$/
        return bin2hex(random_bytes(3));
    }
}
