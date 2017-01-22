<?php
/**
 * @Author: printempw
 * @Date:   2016-11-25 21:26:09
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-17 23:05:33
 */

use App\Models\User;
use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {

    Hook::addMenuItem('admin', 7, [
        'title' => '导入数据',
        'link'  => 'setup/migrations',
        'icon'  => 'fa-undo'
    ]);

    Hook::addRoute(function ($router) {

        $router->group([
            'prefix' => 'setup/migrations',
            'middleware' => ['web', 'auth'],
            'namespace' => 'Blessing\ImportV2Data'
        ], function () use ($router)
        {
            $router->get ('/',       'ImportController@welcome');
            $router->get ('/import', 'ImportController@import');
            $router->post('/import', 'ImportController@finish');
        });
    });
};
