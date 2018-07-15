<?php

namespace Integration\CrazyLogin\Cipher;

use App\Services\Cipher\BaseCipher;

class CrazyCrypt1 extends BaseCipher
{
    /**
     * Once SHA512 hash
     */
    public function hash($value, $salt = "")
    {
        $username = $salt;

        $text = "ÜÄaeut//&/=I " . $value . "7421€547" . $username . "__+IÄIH§%NK " . $value;
        $t1 = unpack("H*", $text);
        $t2 = substr($t1[1], 0, mb_strlen($text, 'UTF-8')*2);
        $t3 = pack("H*", $t2);

        return hash('sha512', $t3);
    }
}
