<?php

namespace Yggdrasil\Utils;

use Yggdrasil\Exceptions\IllegalArgumentException;

class Key
{
    public static function getPrivateKey($key)
    {
        $privateKey = openssl_pkey_get_private(option('ygg_private_key'));

        if (!$privateKey) {
            throw new IllegalArgumentException(trans('Yggdrasil::config.rsa.invalid'));
        }

        return $privateKey;
    }

    public static function getPublicKey($key)
    {
        $keyData = openssl_pkey_get_details($key);

        if ($keyData['bits'] < 4096) {
            throw new IllegalArgumentException(trans('Yggdrasil::config.rsa.length'));
        }

        return $keyData['key'];
    }
}
