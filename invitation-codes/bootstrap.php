<?php

use Carbon\Carbon;
use App\Services\Hook;
use App\Events\UserRegistered;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    // 因为其他插件也有可能修改 POST auth/register 的路由，
    // 所以我们等到 Application 启动后（其他插件的路由注册已完毕）后再给路由添加中间件。
    App::booted(function () {
        app('router')->getRoutes()->get('POST')['auth/register']->middleware([
            InvitationCodes\CheckInvitationCode::class
        ]);
    });

    Hook::addScriptFileToPage(plugin('invitation-codes')->assets('register.js'), [
        'auth/register'
    ]);

    Hook::addRoute(function ($router) {
        $router->group([
            'middleware' => ['web', 'auth', 'admin'],
            'namespace'  => 'InvitationCodes',
        ], function ($router) {
            $router->any('admin/invitation-codes', 'InvitationCodeController@generate');
        });
    });

    Hook::addMenuItem('admin', 3, [
        'title' => '邀请码',
        'link'  => 'admin/invitation-codes',
        'icon'  => 'fa-inbox'
    ]);

    $events->listen(UserRegistered::class, function ($event) {
        // 用户注册后标记该邀请码为已使用
        DB::table('invitation_codes')->where('code', session('using_invitation_code'))->update([
            'used_by' => $event->user->uid,
            'used_at' => Carbon::now(),
        ]);
    });
};
