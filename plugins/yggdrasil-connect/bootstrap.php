<?php

use App\Models\User;
use App\Services\Facades\Option;
use App\Services\Hook;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use LittleSkin\YggdrasilConnect\Console\CreatePersonalAccessClient;
use LittleSkin\YggdrasilConnect\Console\FixUUIDTable;
use LittleSkin\YggdrasilConnect\Models\AccessToken;
use LittleSkin\YggdrasilConnect\Models\UUID;
use LittleSkin\YggdrasilConnect\Scope;

require __DIR__.'/src/Utils/helpers.php';

return function (Dispatcher $events, Filter $filter, Request $request) {
    Passport::personalAccessTokensExpireIn(now()->addSeconds(Option::get('ygg_token_expire_1')));

    if (env('YGG_VERBOSE_LOG')) {
        config(['logging.channels.ygg' => [
            'driver' => 'daily',
            'days' => env('YGG_VERBOSE_LOG_DAYS'),
            'path' => storage_path('logs/yggdrasil.log'),
        ]]);
    } else {
        config(['logging.channels.ygg' => [
            'driver' => 'monolog',
            'handler' => Monolog\Handler\NullHandler::class,
        ]]);
    }

    // 记录访问详情
    if (request()->is('api/yggdrasil/*')) {
        ygg_log_http_request_and_response();
    }

    $filter->add('can_add_player', function (bool $can, string $name) {
        if (option('ygg_uuid_algorithm') === 'v3') {
            $uuid = UUID::generateUuidV3($name);
            if (UUID::where('uuid', $uuid)->orWhere('name', $name)->count()) {
                return new Rejection('UUID 表数据错误，请联系站点管理员处理');
            }
        } else {
            if (UUID::where('name', $name)->count()) {
                return new Rejection('UUID 表数据错误，请联系站点管理员处理');
            }
        }

        return $can;
    });

    // 向用户中心首页添加「快速配置启动器」板块
    if (option('ygg_show_config_section')) {
        $filter->add('grid:user.index', function ($grid) {
            $grid['widgets'][0][0][] = 'LittleSkin\YggdrasilConnect::dnd';

            return $grid;
        });
        Hook::addScriptFileToPage(plugin('yggdrasil-connect')->assets('dnd.js'), ['user']);
    }

    $events->listen(ArtisanStarting::class, function (ArtisanStarting $event) {
        $event->artisan->resolveCommands([
            CreatePersonalAccessClient::class,
            FixUUIDTable::class,
        ]);
    });
    $events->listen('user.profile.updated', function (User $user, string $action) {
        if ($action === 'password' || $action === 'email') {
            AccessToken::revokeAllForUser($user);
        }
    });
    $events->listen('player.added', 'LittleSkin\\YggdrasilConnect\\Listeners\\OnPlayerAdded@handle');
    $events->listen('player.renamed', 'LittleSkin\\YggdrasilConnect\\Listeners\\OnPlayerRenamed@handle');

    if (env('YGG_VERBOSE_LOG')) {
        Hook::addMenuItem('admin', 4, [
            'title' => 'LittleSkin\\YggdrasilConnect::log.title',
            'link' => 'admin/yggdrasil-log',
            'icon' => 'fa-history',
        ]);
    }

    Hook::addRoute(function () {
        Route::namespace('LittleSkin\YggdrasilConnect\Controllers')
            ->group(function () {
                Route::prefix('api/yggdrasil')->group(__DIR__.'/routes.php');

                Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
                    Route::middleware('role:admin')
                        ->group(function () {
                            Route::get('yggdrasil-log', 'ConfigController@logPage');
                            Route::post(
                                'plugins/config/yggdrasil-connect/generate',
                                'ConfigController@generate'
                            );
                        });
                });
                Route::prefix('yggc')->group(function () {
                    Route::middleware(['web', 'LittleSkin\YggdrasilConnect\Middleware\CheckIsIssuerSet', 'auth'])->group(function () {
                        Route::get('callback', 'OIDCController@passportCallback');
                        Route::post('callback', 'OIDCController@selectProfile');
                        Route::get('cancel', 'OIDCController@cancel');
                    });
                    // Route::get('device', 'OIDCController@userCode');
                    Route::middleware(['api', 'LittleSkin\YggdrasilConnect\Middleware\CheckBearerTokenOAuth:'.Scope::OPENID])
                        ->get('userinfo', 'OIDCController@getUserInfo');
                });
            });
    });

    Hook::pushMiddleware('LittleSkin\\YggdrasilConnect\\Middleware\\HandleCors'); // https://t.me/blessing_skin/184887

    if (option('ygg_enable_ali')) {
        Hook::pushMiddleware('LittleSkin\YggdrasilConnect\Middleware\AddApiIndicationHeader');
    }

    if ($request->is('oauth/authorize')) {
        Hook::pushMiddleware('LittleSkin\YggdrasilConnect\Middleware\CheckIfScopeValid');
    }

    Client::retrieved(function (Client $client) use ($request) {
        if ($request->is('oauth/authorize')) {
            $yggc_redirect = option('site_url').'/yggc/callback';
            if (!in_array($yggc_redirect, explode(',', $client->redirect))) {
                $client->redirect = "$client->redirect,$yggc_redirect";
            }
        }
    });
};
