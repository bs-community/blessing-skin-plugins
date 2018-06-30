<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    // 说实话，太沙雕了。
    Hook::addRoute(function ($router) {
        $router->get('/raw/{tid}.png', function () {
            abort(403, '根据本站设置，你无法直接下载皮肤文件。');
        });
    });
};
