<?php

use App\Services\Hook;
use Blessing\Filter;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Filter $filter) {
    Hook::addScriptFileToPage(
        plugin('invitation-codes')->assets('register.js'),
        ['auth/register']
    );

    Hook::addRoute(function () {
        Route::namespace('InvitationCodes')
            ->middleware(['web', 'auth', 'role:admin'])
            ->prefix('admin/invitation-codes')
            ->group(function () {
                Route::get('', 'InvitationCodeController@list');
                Route::post('generate', 'InvitationCodeController@generate');
            });
    });

    Hook::addMenuItem('admin', 4, [
        'title' => '邀请码',
        'link' => 'admin/invitation-codes',
        'icon' => 'fa-inbox',
    ]);

    $filter->add('can_register', InvitationCodes\CheckInvitationCode::class);

    $events->listen('auth.registration.completed', function ($user) {
        // 用户注册后标记该邀请码为已使用
        DB::table('invitation_codes')
            ->where('code', session()->pull('using_invitation_code'))
            ->update([
                'used_by' => $user->uid,
                'used_at' => Carbon::now(),
            ]);
    });
};
