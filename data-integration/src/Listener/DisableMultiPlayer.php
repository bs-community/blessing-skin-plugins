<?php
/**
 * @Author: printempw
 * @Date:   2016-11-16 21:38:33
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 09:58:19
 */

namespace DataIntegration\Listener;

use View;
use App\Events;
use App\Services\Hook;
use DataIntegration\Log;
use DataIntegration\Utils as MyUtils;
use Illuminate\Contracts\Events\Dispatcher;

class DisableMultiPlayer
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\PlayerWillBeAdded::class, function($event) {
            exit(json('启用数据对接时无法添加新角色。', 1)->getContent());
        });

        $events->listen(Events\PlayerWillBeDeleted::class, function($event) {
            exit(json('启用数据对接时无法删除角色。', 1)->getContent());
        });

        $events->listen(Events\PlayerWillBeDeleted::class, function($event) {
            exit(json('启用数据对接时无法删除角色。', 1)->getContent());
        });

        $events->listen([
            Events\UserAuthenticated::class,
            // Events\UserTryToLogin::class
        ], 'DataIntegration\Controllers\AuthController@determineUniqueUsername');

        // Hijack views
        View::alias('DataIntegration::register', 'auth.register');

        // Delete menu item links to of /user/player
        config(['menu.user' => collect(config('menu.user'))->reject(function ($item) {
            return $item['link'] == 'user/player';
        })->all()]);

        Hook::addRoute(function ($router) {
            $router->any('/user', 'DataIntegration\Controllers\UserController@index')
                ->middleware(['web', 'auth']);
            $router->any('/user/closet', 'DataIntegration\Controllers\UserController@closet')
                ->middleware(['web', 'auth']);
            $router->post('/auth/register', 'DataIntegration\Controllers\AuthController@handleRegister')
                ->middleware(['web']);
        });
    }
}
