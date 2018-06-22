<?php

use App\Services\Hook;
use Yggdrasil\Utils\Log as MyLog;
use Illuminate\Contracts\Events\Dispatcher;
use Yggdrasil\Exceptions\IllegalArgumentException;

return function (Dispatcher $events) {

    // 创建数据表
    if (! Schema::hasTable('uuid')) {
        Schema::create('uuid', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('uuid', 255);
        });
    }

    // 初始化配置项
    $items = [
        'ygg_token_expire_1' => '600',
        'ygg_token_expire_2' => '1200',
        'ygg_rate_limit' => '1000',
        'ygg_skin_domain' => '',
        'ygg_search_profile_max' => '5',
        'ygg_verbose_log' => 'true'
    ];

    foreach ($items as $key => $value) {
        if (! Option::has($key)) {
            Option::set($key, $value);
        }
    }

    // 记录访问详情
    $request = app('request');
    if ($request->is('api/yggdrasil/*')) {
        MyLog::info('============================================================');
        MyLog::info($request->method(), [$request->path(), $request->json()->all()]);
    }

    // App\Http\Middleware\RedirectIfUrlEndsWithSlash
    Hook::addRoute(function ($router) {
        $router->any('api/yggdrasil', 'Yggdrasil\Controllers\AuthController@hello');

        $router->post('admin/plugins/config/yggdrasil-api/generate', 'Yggdrasil\Controllers\AuthController@generate')->middleware(['web', 'auth', 'admin']);

        $router->group([
            'middleware' => ['web'],
            'namespace'  => 'Yggdrasil\Controllers',
            'prefix' => 'api/yggdrasil'
        ], function ($router) {
            require __DIR__.'/routes.php';
        });
    });
};
