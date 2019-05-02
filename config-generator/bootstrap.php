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
        // pass it to a controller instead of a closure for route cache
        $router->view('/user/config', 'Blessing\ConfigGenerator::config')->middleware(['web', 'auth']);
    });
};
