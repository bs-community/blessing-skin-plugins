<?php

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/Utils/helpers.php';

return function (Dispatcher $events) {

    // 创建数据表
    ygg_init_db_tables();

    // 从旧版升级上来的默认继续使用旧的 UUID 生成算法
    if (DB::table('uuid')->count() > 0 && !Option::has('ygg_uuid_algorithm')) {
        Option::set('ygg_uuid_algorithm', 'v4');
    }

    // 初始化配置项
    ygg_init_options();

    // 初次使用自动生成私钥
    if (option('ygg_private_key') == '') {
        option(['ygg_private_key' => ygg_generate_rsa_keys()['private']]);
    }

    // 记录访问详情
    if (request()->is('api/yggdrasil/*')) {
        ygg_log_http_request_and_response();
    }

    // 向用户中心首页添加「快速配置启动器」板块
    if (option('ygg_show_config_section')) {
        $events->listen(App\Events\RenderingHeader::class, function ($event) {
            $event->addContent('<script src="https://cdn.bootcss.com/clipboard.js/2.0.1/clipboard.min.js"></script>');
        });
        Hook::addScriptFileToPage(plugin('yggdrasil-api')->assets('assets/dist/dnd.js'), ['user']);
    }

    // 添加 API 路由
    Hook::addRoute(function ($router) {
        $router->any('api/yggdrasil', 'Yggdrasil\Controllers\ConfigController@hello');

        $router->group([
            'middleware' => ['web', 'auth', 'admin'],
            'namespace'  => 'Yggdrasil\Controllers',
            'prefix' => 'admin/plugins/config/yggdrasil-api'
        ], function ($router) {
            $router->post('import', 'ConfigController@import');
            $router->post('generate', 'ConfigController@generate');
        });

        $router->group([
            'middleware' => ['web'],
            'namespace'  => 'Yggdrasil\Controllers',
            'prefix' => 'api/yggdrasil'
        ], function ($router) {
            require __DIR__.'/routes.php';
        });
    });
};
