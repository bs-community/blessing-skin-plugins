<?php

namespace Yggdrasil\Controllers;

use Illuminate\Routing\Controller;
use Yggdrasil\Utils\Key;

class ServicesController extends Controller
{
    public function getPublicKeys()
    {
        $privateKey = Key::getPrivateKey(config('ygg_private_key'));

        $result = [
            'profilePropertyKeys' => [
                [
                    'publicKey' => Key::getPublicKey($privateKey),
                ],
            ],
            'playerCertificateKeys' => [
                [
                    'pulicKey' => Key::getPublicKey($privateKey),
                ],
            ],
        ];

        return json($result);
    }
}
