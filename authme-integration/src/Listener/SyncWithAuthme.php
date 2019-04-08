<?php

namespace Integration\Authme\Listener;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Player;
use App\Events\UserTryToLogin;
use App\Events\UserAuthenticated;
use Illuminate\Contracts\Events\Dispatcher;

class SyncWithAuthme
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        // 初始化在 Authme 那边注册的用户
        User::where('realname', '<>', '')
            ->get()
            ->filter(function ($user) {
                return $user->players->count() == 0;
            })
            ->each(function ($user) {
                $user->nickname = $user->realname;
                $user->score = option('user_initial_score');
                $user->register_at = get_datetime_string();
                $user->last_sign_at = get_datetime_string(time() - 86400);
                $user->save();

                $player = new Player;
                $player->name = $user->realname;
                $player->uid = $user->uid;
                $player->tid_skin = 0;
                $player->save();
            });

        // 保证 BS 绑定的角色名与 Authme 同步
        $events->listen(UserAuthenticated::class, function ($event) {
            $user = $event->user;
            $user->realname = $user->player_name;
            $user->username = strtolower($user->player_name);
            $user->regdate = Carbon::parse($user->register_at)->timestamp * 1000;
            $user->save();
        });
    }
}
