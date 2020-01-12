<?php

use App\Events\ConfigureRoutes;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router;

return function (Dispatcher $events, Router $router) {
    $events->listen(ConfigureRoutes::class, function () use ($router) {
        $routes = $router->getRoutes()->get('GET');
        $routes['{player}.json']->middleware([Blessing\NoContent\Middleware::class]);
        $routes['csl/{player}.json']->middleware([Blessing\NoContent\Middleware::class]);
    });
};
