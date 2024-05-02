<?php

return function (App\Services\PluginManager $plugins) {
    $authme = $plugins->get('authme-integration');
    $isAuthMe = $authme && $authme->isEnabled();
    $authMeAlgs = ['SALTED2MD5', 'SALTED2SHA512', 'SHA256'];

    $methods = preg_split('/,\s*/', env('PASSWORD_METHODS'));
    $ciphers = array_map(function ($method) use ($isAuthMe, $authMeAlgs) {
        if ($isAuthMe && in_array($method, $authMeAlgs)) {
            return 'Integration\Authme\Cipher\\'.$method;
        } else {
            return 'App\Services\Cipher\\'.$method;
        }
    }, $methods);
    app()->tag($ciphers, 'ciphers');

    app()->instance(
        'cipher',
        new GPlane\PasswordTransition\Cipher(preg_split('/,\s*/', env('SALT')))
    );
};
