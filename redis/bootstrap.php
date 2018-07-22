<?php

return function () {
    // 兼容 BS <= 3.4.0
    if (menv('REDIS_SCHEME') == 'unix') {
        $config = array_replace(config('database.redis.default'), [
            'scheme' => 'unix',
            'path' => menv('REDIS_SOCKET_PATH'),
        ]);

        config(['database.redis.default' => $config]);
    }

    try {
        if (Predis::connection()->ping()) {
            config(['cache.default'  => 'redis']);
            config(['session.driver' => 'redis']);
        }
    } catch (Exception $e) {
        //
    }
};
