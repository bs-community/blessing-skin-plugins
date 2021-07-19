<?php

use App\Models\User;
use App\Services\Hook;
use App\Services\Plugin;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Route as RouteItem;
use Illuminate\Support\Str;

return function (
    Filter $filter,
    Dispatcher $events,
    Plugin $plugin
) {
    $filter->add('can_add_player', function () {
        return new Rejection(trans('SinglePlayerLimit::player.add'));
    });

    $filter->add('can_delete_player', function () {
        return new Rejection(trans('SinglePlayerLimit::player.delete'));
    });

    $filter->add('user_can_edit_profile', function ($can, $action) {
        if ($action === 'nickname') {
            return new Rejection(trans('SinglePlayerLimit::user.nickname'));
        }

        return $can;
    });

    $events->listen('player.renamed', function ($player) {
        /** @var User */
        $user = $player->user;
        $user->nickname = $player->name;
        $user->save();
    });

    Hook::addScriptFileToPage($plugin->assets('BindPlayer.js'), ['user/player/bind']);
    Hook::addRoute(function () {
        Route::namespace('SinglePlayerLimit')
            ->middleware(['web', 'authorize'])
            ->prefix('user/player/bind')
            ->group(function () {
                Route::view('', 'SinglePlayerLimit::bind');
                Route::post('', 'BindController@bind');
            });

        $routes = Route::getRoutes()->getRoutes();
        $routes = array_filter($routes, function (RouteItem $route) {
            return Str::startsWith($route->uri(), 'user');
        });
        array_walk($routes, function (RouteItem $route) {
            $route->middleware(SinglePlayerLimit\RequireBindPlayer::class);
        });
    });
};
