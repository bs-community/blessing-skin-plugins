<?php

$router->group([
    'prefix' => 'authserver',
    'middleware' => ['Yggdrasil\Middleware\CheckContentType'],
], function ($router) {
    // 防止暴力破解密码
    $router->group([
        'middleware' => ['Yggdrasil\Middleware\Throttle'],
    ], function ($router) {
        $router->post('authenticate', 'AuthController@authenticate');
        $router->post('signout', 'AuthController@signout');
    });

    $router->post('refresh', 'AuthController@refresh');

    $router->post('validate', 'AuthController@validate');
    $router->post('invalidate', 'AuthController@invalidate');
});

$router->group([
    'prefix' => 'sessionserver/session/minecraft'
], function ($router) {
    $router->post('join', 'SessionController@joinServer');
    $router->get('hasJoined', 'SessionController@hasJoinedServer');

    $router->get('profile/{uuid}', 'ProfileController@getProfileFromUuid');
    $router->get('profile/username/{name}', 'ProfileController@getProfileFromName');
});

$router->post('api/profiles/minecraft', 'ProfileController@searchProfile');
