<?php

use App\Services\Hook;

return function () {
    Hook::addScriptFileToPage(plugin_assets('share-registration-link', 'generate.js'), ['user']);
    Hook::addScriptFileToPage(plugin_assets('share-registration-link', 'registration.js'), ['auth/register']);

    Hook::addRoute(function ($router) {
        $router->group([
            'middleware' => ['web', 'auth'],
            'namespace'  => 'GPlane\ShareRegistrationLink',
            'prefix' => '/user/reg-links',
        ], function ($router) {
            $router->get('', 'CodeController@list');
            $router->post('', 'CodeController@generate');
            $router->post('remove', 'CodeController@remove');
        });
    });

    App::booted(function () {
        app('router')->getRoutes()->get('POST')['auth/register']->middleware([
            GPlane\ShareRegistrationLink\CheckCode::class
        ]);
    });
};
