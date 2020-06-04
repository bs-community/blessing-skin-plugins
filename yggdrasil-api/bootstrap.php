<?php

use App\Services\Hook;
use Blessing\Filter;

require __DIR__.'/src/Utils/helpers.php';

return function (Filter $filter) {
    if (env('YGG_VERBOSE_LOG')) {
        config(['logging.channels.ygg' => [
            'driver' => 'single',
            'path' => ygg_log_path(),
        ]]);
    } else {
        config(['logging.channels.ygg' => [
            'driver' => 'monolog',
            'handler' => Monolog\Handler\NullHandler::class,
        ]]);
    }

    // 从旧版升级上来的默认继续使用旧的 UUID 生成算法
    if (DB::table('uuid')->count() > 0 && !Option::get('ygg_uuid_algorithm')) {
        Option::set('ygg_uuid_algorithm', 'v4');
    }

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
        $new = $model->getAttribute('name');
        $original = $model->getOriginal('name');

        if (!$original || $original === $new) {
            return;
        }

        // 要是能执行到这里就说明新的角色名已经没人在用了
        // 所以残留着的 UUID 映射删掉也没问题
        DB::table('uuid')->where('name', $new)->delete();
        DB::table('uuid')->where('name', $original)->update(['name' => $new]);
    };

    // 仅当 UUID 生成算法为「随机生成」时保证修改角色名后 UUID 一致
    // 因为另一种 UUID 生成算法要最大限度兼容盗版模式，所以不做修改
    if (option('ygg_uuid_algorithm') == 'v4') {
        App\Models\Player::updating($callback);
    }

    // 向用户中心首页添加「快速配置启动器」板块
    if (option('ygg_show_config_section')) {
        $filter->add('grid:user.index', function ($grid) {
            $grid['widgets'][0][0][] = 'Yggdrasil::dnd';

            return $grid;
        });
        Hook::addScriptFileToPage(plugin('yggdrasil-api')->assets('dnd.js'), ['user']);
    }

    // 向管理后台菜单添加「Yggdrasil 日志」项目
    Hook::addMenuItem('admin', 4, [
        'title' => 'Yggdrasil::log.title',
        'link' => 'admin/yggdrasil-log',
        'icon' => 'fa-history',
    ]);

    // 添加 API 路由
    Hook::addRoute(function () {
        Route::namespace('Yggdrasil\Controllers')
            ->prefix('api/yggdrasil')
            ->group(function () {
                Route::any('', 'ConfigController@hello');

                require __DIR__.'/routes.php';
            });

        Route::middleware(['web', 'auth', 'role:admin'])
            ->namespace('Yggdrasil\Controllers')
            ->prefix('admin')
            ->group(function () {
                Route::get('yggdrasil-log', 'ConfigController@logPage');

                Route::post(
                    'plugins/config/yggdrasil-api/generate',
                    'ConfigController@generate'
                );
            });
    });

    // 全局添加 ALI HTTP 响应头
    if (option('ygg_enable_ali')) {
        $kernel = app()->make(Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(Yggdrasil\Middleware\AddApiIndicationHeader::class);
    }
};
