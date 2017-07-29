<?php

$router->any('/', function () {
    return json([
        'name' => 'Yggdrasil API for Blessing Skin',
        'version' => plugin('yggdrasil-api')['version']
    ]);
});

// Profiles
$router->get('profile/{uuid}', 'ProfileController@getProfileFromUuid');
$router->get('profile/username/{username}', 'ProfileController@getProfileFromName');
$router->post('profiles', 'ProfileController@searchProfile');

// 配合 authlib-agent
$router->get('profiles/minecraft/{uuid}', 'ProfileController@getProfileFromUuid');
$router->get('username2profile/{username}', 'ProfileController@getProfileFromName');
$router->post('profilerepo', 'ProfileController@searchProfile');
$router->get('hasJoined', 'ServerController@hasJoinedServer');
$router->get('hasjoinserver', 'ServerController@hasJoinedServer');

$router->group([
    'middleware' => ['Yggdrasil\Middleware\CheckContentType'],
], function ($router) {
    $router->post('authenticate', 'AuthController@authenticate');
    $router->post('refresh', 'AuthController@refresh');

    $router->post('validate', 'AuthController@validate');
    $router->post('invalidate', 'AuthController@invalidate');

    $router->post('signout', 'AuthController@signout');

    $router->post('join', 'ServerController@joinServer');

    // 下面几个路由是为了配合 authlib-agent 的路由规则
    $router->post('joinserver', 'ServerController@joinServer');
});
