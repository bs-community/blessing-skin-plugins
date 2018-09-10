<?php

namespace Integration\Authme\Listener;

use App\Events\UserAuthenticated;
use App\Events\UserTryToLogin;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Events\Dispatcher;
use Utils;

class SyncWithAuthme
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        // 初始化在 Authme 那边注册的用户
        DB::table('users')->where('player_name', '')->whereNotNull('realname')->update([
            'player_name'  => DB::raw('`realname`'),
            'nickname'     => DB::raw('`realname`'),
            'score'        => option('user_initial_score'),
            'register_at'  => Utils::getTimeFormatted(),
            'last_sign_at' => Utils::getTimeFormatted(time() - 86400),
        ]);

        // 在 Authme 那边注册的用户虽然有定义了 player_name
        // 但是 players 表中没有相应的记录，所以使用角色名登录时会提示用户不存在
        $events->listen(UserTryToLogin::class, function ($event) {
            $user = User::where('player_name', $event->identification)->first();
            if (!$user) {
                return;
            }
            // 触发一下事件，让「单角色限制」插件那边帮我们把 player 准备好
            event(new UserAuthenticated($user));
        });

        // 保证 BS 绑定的角色名与 Authme 同步
        $events->listen(UserAuthenticated::class, function ($event) {
            $user = $event->user;
            // 字段 player_name 是由「单角色限制」插件维护的
            $user->realname = $user->player_name;
            $user->username = strtolower($user->player_name);
            $user->regdate = Carbon::parse($user->register_at)->timestamp * 1000;
            $user->save();
        });
    }
}
