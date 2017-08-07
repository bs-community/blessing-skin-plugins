<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    Hook::registerPluginTransScripts('config-generator');

    $index = (plugin('data-integration') && plugin('data-integration')->isEnabled()) ? 2 : 3;

    Hook::addMenuItem('user', $index, [
        'title' => 'Blessing\ConfigGenerator::config.generate-config',
        'link'  => 'user/config',
        'icon'  => 'fa-book'
    ]);

    Hook::addRoute(function ($router) {
        // pass it to a controller instead of a closure for route cache
        $router->get('/user/config', 'Blessing\ConfigGenerator\ConfigController@show')->middleware(['web', 'auth']);
    });
};
