<?php
/**
 * @Author: printempw
 * @Date:   2017-01-18 13:00:46
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-18 17:26:44
 */
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $kernel = app()->make('Illuminate\Contracts\Http\Kernel');
    $kernel->pushMiddleware('Blessing\CDN\ModifyStaticFileUrl');
};
