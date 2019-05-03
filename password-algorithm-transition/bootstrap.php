<?php

return function (App\Services\PluginManager $plugins) {
    $newAlgorithm = env('PWD_METHOD');
    $oldAlgorithm = env('OLD_PWD_METHOD');

    $isAuthMe = $plugins->isEnabled('authme-integration');
    $authMeAlgs = ['SALTED2MD5', 'SALTED2SHA512', 'SHA256'];

    if ($isAuthMe && in_array($oldAlgorithm, $authMeAlgs)) {
        app()->singleton('cipher.old', 'Integration\Authme\Cipher\\'.$oldAlgorithm);
    } else {
        app()->singleton('cipher.old', 'App\Services\Cipher\\'.$oldAlgorithm);
    }

    if ($isAuthMe && in_array($newAlgorithm, $authMeAlgs)) {
        app()->singleton('cipher.new', 'Integration\Authme\Cipher\\'.$newAlgorithm);
    } else {
        app()->singleton('cipher.new', 'App\Services\Cipher\\'.$newAlgorithm);
    }

    app()->singleton('cipher', \GPlane\PasswordTransition\Cipher::class);
};
