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

    // 保证用户修改角色名后 UUID 一致
    $callback = function ($model) {
        $new = $model->getAttribute('player_name');
        $original = $model->getOriginal('player_name');

        if (!$original || $original === $new) return;

        // 要是能执行到这里就说明新的角色名已经没人在用了
        // 所以残留着的 UUID 映射删掉也没问题
        DB::table('uuid')->where('name', $new)->delete();
        DB::table('uuid')->where('name', $original)->update(['name' => $new]);
    };

    // 仅当 UUID 生成算法为「随机生成」时保证修改角色名后 UUID 一致
    // 因为另一种 UUID 生成算法要最大限度兼容盗版模式，所以不做修改
    if (option('ygg_uuid_algorithm') == 'v4') {
        // 兼容「单角色限制」插件
        $plugin = plugin('single-player-limit');

        if ($plugin && $plugin->isEnabled()) {
            App\Models\User::updating($callback);
        } else {
            App\Models\Player::updating($callback);
        }
    }

    // 向用户中心首页添加「快速配置启动器」板块
    if (option('ygg_show_config_section')) {
        $events->listen(App\Events\RenderingHeader::class, function ($event) {
            $event->addContent('<script src="https://cdn.bootcss.com/clipboard.js/2.0.1/clipboard.min.js"></script>');
        });
        Hook::addScriptFileToPage(plugin('yggdrasil-api')->assets('assets/dist/dnd.js'), ['user']);
    }

    // 向用户中心首页添加「最近活动」板块
    if (option('ygg_show_activities_section')) {
        Hook::addScriptFileToPage(plugin('yggdrasil-api')->assets('assets/dist/recent.js'), ['user']);
    }

    // 向管理后台菜单添加「Yggdrasil 日志」项目
    Hook::addMenuItem('admin', 3, [
        'title' => 'Yggdrasil 日志',
        'link'  => 'admin/yggdrasil-log',
        'icon'  => 'fa-history'
    ]);

    // 添加 API 路由
    Hook::addRoute(function ($router) {

        $router->group([
            'namespace'  => 'Yggdrasil\Controllers'
        ], function ($router) {
            $router->any('api/yggdrasil', 'ConfigController@hello');
            $router->get('user/get-recent-activities', 'ConfigController@getRecentActivities')->middleware(['web', 'auth']);
        });

        $router->group([
            'middleware' => ['web', 'auth', 'admin'],
            'namespace'  => 'Yggdrasil\Controllers'
        ], function ($router) {
            $router->get('admin/yggdrasil-log', 'ConfigController@log');
            $router->get('admin/yggdrasil-log/data', 'ConfigController@logData');
        });

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

    // 全局添加 ALI HTTP 响应头
    if (option('ygg_enable_ali')) {
        $kernel = app()->make(Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(Yggdrasil\Middleware\AddApiIndicationHeader::class);
    }
};
