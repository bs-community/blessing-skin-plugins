<?php

namespace Integration\Forum\Listener;

use App\Events;
use App\Models\User;
use App\Models\Player;
use Illuminate\Contracts\Events\Dispatcher;

class SynchronizeUser
{
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
            if ($event->authType == 'email') {
                $user = User::where('email', $event->identification)->first();
            } else {
                $player = Player::where('name', $event->identification)->first();
                $user = optional($player, function ($p) {
                    return $p->user;
                });
            }

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

        // 如果两边用户的密码或 salt 不同，就按照「重复处理」选项的定义来处理。
        if (
            $user->password != $remoteUser->password ||
            (! empty($remoteUser->salt) && $user->salt != $remoteUser->salt)
        ) {
            if (option('forum_duplicated_prefer') == 'remote') {
                $user->password = $remoteUser->password;
                $user->salt = $remoteUser->salt;
                $user->save();
            } else {
                app('db.remote')->where('email', $user->email)->update([
                    'password' => $user->password,
                    'salt' => $user->salt
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
        if (config('secure.cipher') == 'PHP_PASSWORD_HASH') {
            // 用这个加密算法说明正在使用 Flarum
            app('db.remote')->insertGetId([
                'username' => $user->player_name,
                'email'    => $user->email,
                'password' => $user->password,
                'is_email_confirmed' => $user->verified
            ]);
        } elseif (config('secure.cipher') == 'SALTED2MD5') {
            // 用这个加密算法说明正在使用 Discuz! 或 PhpWind
            app('db.remote')->insertGetId([
                'username' => $user->player_name,
                'email'    => $user->email,
                'password' => $user->password,
                'regip'    => $user->ip,
                'regdate'  => time(),
                'salt'     => $user->salt
            ]);
        }

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
        $user->ip           = $result->regip ?? '255.255.255.255';
        $user->score        = option('user_initial_score');
        $user->register_at  = get_datetime_string();
        $user->last_sign_at = get_datetime_string(time() - 86400);
        $user->permission   = User::NORMAL;
        $user->nickname     = $result->username;
        $user->player_name  = $result->username;
        $user->salt         = $result->salt ?? '';
        $user->save();
        event(new Events\UserRegistered($user));

        $user->refresh();
        if ($player = Player::where('name', $result->username)->first()) {
            // 保证角色为该用户所有
            $player->uid = $user->uid;
            $player->save();
        } else {
            $player = new Player;
            $player->uid = $user->uid;
            $player->name = $result->username;
            $player->tid_skin = 0;
            $player->tid_cape = 0;
            $player->last_modified = get_datetime_string();
            $player->save();
            event(new Events\PlayerWasAdded($player));
        }

        return $user;
    }
}
