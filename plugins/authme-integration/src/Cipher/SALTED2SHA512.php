<?php

namespace Integration\Authme\Cipher;

use App\Services\Cipher\BaseCipher;

class SALTED2SHA512 extends BaseCipher
{
    public function hash($value, $salt = ''): string
    {
        return hash('sha512', $value.$salt);
    }

    public function generateSalt()
    {
        // Format /^[0-9a-f]{6}$/
        return bin2hex(random_bytes(3));
    }
}
