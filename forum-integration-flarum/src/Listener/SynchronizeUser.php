<?php

namespace Integration\Forum\Listener;

use Utils;
use App\Events;
use App\Models\User;
use App\Models\Player;
use Illuminate\Contracts\Events\Dispatcher;

class SynchronizeUser
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen([
            Events\UserProfileUpdated::class,
            Events\UserAuthenticated::class,
            Events\UserTryToLogin::class,
            Events\UserRegistered::class,
        ], [$this, 'synchronize']);
    }

    public function synchronize(Events\Event $event)
    {
        if ($event instanceof Events\UserTryToLogin) {
            $user = User::where(
                $event->authType == 'email' ? 'email' : 'player_name',
                $event->identification
            )->first();

            // 如果正在登录的用户在皮肤站数据库中不存在，就尝试从论坛数据库同步过来
            if (! $user) {
                $user = $this->syncFromRemote($event->authType, $event->identification);
            }
        } else {
            $user = $event->user;
        }

        // 如果到这里了皮肤站里还是没有这个用户，就说明该用户确实是不存在的
        if (! $user) return;

        $remoteUser = app('db.remote')->where('email', $user->email)->first();

        // 如果这个角色存在于皮肤站，却不存在与论坛数据库中的话，就尝试同步过去
        if (! $remoteUser) {
            $remoteUser = $this->syncFromLocal($user);
        }

        // 如果到这里论坛数据库里还是没有这个用户，就说明同步失败了，那下面的逻辑也不用执行了。
        if (! $remoteUser) return;

        // 如果两边用户的密码不同，就按照「重复处理」选项的定义来处理。
        if ($user->password != $remoteUser->password) {
            if (option('forum_duplicated_prefer') == 'remote') {
                $user->password = $remoteUser->password;
                $user->save();
            } else {
                app('db.remote')->where('email', $user->email)->update([
                    'password' => $user->password,
                ]);
            }
        }

        // 同理，保证两边的用户名、绑定角色名一致。
        if ($user->player_name != $remoteUser->username) {
            if (option('forum_duplicated_prefer') == 'remote') {
                $user->player_name = $remoteUser->username;
                $user->save();
            } else {
                app('db.remote')->where('email', $user->email)->update([
                    'username' => $user->player_name
                ]);
            }
        }
    }

    /**
     * 同步所给的皮肤站用户至论坛数据库。
     *
     * @param User $user
     * @return stdClass|void
     */
    protected function syncFromLocal(User $user)
    {
        app('db.remote')->insertGetId([
            'username' => $user->player_name,
            'email'    => $user->email,
            'password' => $user->password,
            'regip'    => $user->ip,
            'regdate'  => time(),
        ]);

        return app('db.remote')->where('email', $user->email)->first();
    }

    /**
     * 从论坛数据库查找符合条件的用户数据记录，并同步至皮肤站数据库。
     *
     * @param string $column 用于查找的字段名，username 或者 email。
     * @param string $value  用于查找的字段的记录值。
     * @return User|void
     */
    protected function syncFromRemote($column, $value)
    {
        // 从论坛数据库查找
        $result = app('db.remote')->where($column, $value)->first();

        if (! $result) {
            return;
        }

        // 在皮肤站数据库新建用户及角色
        $user               = new User;
        $user->email        = $result->email;
        $user->password     = $result->password;
        $user->ip           = $result->regip;
        $user->score        = option('user_initial_score');
        $user->register_at  = Utils::getTimeFormatted();
        $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
        $user->permission   = User::NORMAL;
        $user->nickname     = $result->username;
        $user->player_name  = $result->username;
        $user->save();
        event(new Events\UserRegistered($user));

        if ($player = Player::where('player_name', $user->player_name)->first()) {
            // 保证角色为该用户所有
            $player->uid = $user->uid;
            $player->save();
        } else {
            $player                = new Player;
            $player->uid           = $user->uid;
            $player->player_name   = $user->player_name;
            $player->preference    = 'default';
            $player->last_modified = Utils::getTimeFormatted();
            $player->save();
            event(new Events\PlayerWasAdded($player));
        }

        return $user;
    }
}
