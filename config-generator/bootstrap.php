<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    Hook::addMenuItem('user', 3, [
        'title' => 'Blessing\ConfigGenerator::config.generate-config',
        'link'  => 'user/config',
        'icon'  => 'fa-book'
    ]);

    Hook::addRoute(function ($router) {
        $router->get(
            '/user/config',
            'Blessing\ConfigGenerator\Controller@generate'
        )->middleware(['web', 'auth']);
    });
};
