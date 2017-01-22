<?php
/**
 * @Author: printempw
 * @Date:   2017-01-20 21:38:39
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-20 21:44:53
 */

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $kernel = app()->make('Illuminate\Contracts\Http\Kernel');
    $kernel->pushMiddleware('Blessing\RemoveCopy\RemoveFooterCopyright');
};
