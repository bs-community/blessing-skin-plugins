<?php
/**
 * @Author: printempw
 * @Date:   2016-10-25 21:28:34
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-17 22:20:52
 */

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    Hook::registerPluginTransScripts('config-generator');

    Hook::addMenuItem('user', 3, [
        'title' => 'Blessing\ConfigGenerator::config.generate-config',
        'link'  => 'user/config',
        'icon'  => 'fa-book'
    ]);

    Hook::addRoute(function ($router) {
        // pass it to a controller instead of a closure for route cache
        $router->get('/user/config', 'Blessing\ConfigGenerator\ConfigController@show')->middleware(['web', 'auth']);
    });
};
