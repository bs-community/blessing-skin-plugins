<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    Hook::registerPluginTransScripts('config-generator', ['user/config']);

    $index = (plugin('data-integration') && plugin('data-integration')->isEnabled()) ? 2 : 3;

    Hook::addMenuItem('user', $index, [
        'title' => 'Blessing\ConfigGenerator::config.generate-config',
        'link'  => 'user/config',
        'icon'  => 'fa-book'
    ]);

    Hook::addRoute(function ($router) {
        // pass it to a controller instead of a closure for route cache
        $router->view('/user/config', 'Blessing\ConfigGenerator::config')->middleware(['web', 'auth']);
    });
};
