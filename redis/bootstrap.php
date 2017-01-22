<?php
/**
 * @Author: printempw
 * @Date:   2016-12-31 14:10:18
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-31 15:05:56
 */

use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {

    try {
        if (Predis::connection()->ping()) {
            config(['cache.default' => 'redis']);
            config(['session.driver' => 'redis']);
        }
    } catch (Exception $e) {
    }
};
