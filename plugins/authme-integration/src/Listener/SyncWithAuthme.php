<?php

namespace Integration\Authme\Listener;

use App\Events\UserAuthenticated;
use App\Models\Player;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;

class SyncWithAuthme
{
    public function subscribe(Dispatcher $events)
    {
        // 初始化在 Authme 那边注册的用户
        User::where('realname', '<>', '')
            ->join('players', 'users.uid', 'players.uid')
            ->groupBy('players.uid')
            ->havingRaw('COUNT(pid) <> 0')
            ->select('users.*')
            ->get()
            ->each(function ($user) {
                $user->nickname = $user->realname;
                $user->score = option('user_initial_score');
                $user->register_at = Carbon::now();
                $user->last_sign_at = Carbon::now()->subDay();
                $user->save();

                $player = new Player();
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
