<?php

namespace Integration\Forum\Listener;

use App\Events;
use App\Models\Player;
use App\Models\User;
use Carbon\Carbon;
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
            if (!$user) {
                $user = $this->syncFromRemote($event->authType, $event->identification);
            }
        } else {
            $user = $event->user;
        }

        // 如果到这里了皮肤站里还是没有这个用户，就说明该用户确实是不存在的
        if (!$user) {
            return;
        }

        $remoteDB = app('db.remote');
        //使用该算法，则确定是discuz,否则为Flarum
        $remoteUser = null;
        if ($this->distinguishForumType() == 'discuz') {
            $remoteUser = $remoteDB->where('uid', $user->forum_uid)->first();
        } else {
            $remoteUser = $remoteDB->where('id', $user->forum_uid)->first();
        }

        // 如果这个角色存在于皮肤站，却不存在与论坛数据库中的话，就尝试同步过去
        if (!$remoteUser) {
            $remoteUser = $this->syncFromLocal($user);
        }

        // 如果到这里论坛数据库里还是没有这个用户，就说明同步失败了，那下面的逻辑也不用执行了。
        if (!$remoteUser) {
            return;
        }

        // 首先同步邮箱
        if (
            $user->email != $remoteUser->email &&
            !empty($user->email) &&
            !empty($remoteUser->email)
        ) {
            if (option('forum_duplicated_prefer') == 'remote') {
                $user->email = $remoteUser->email;
                $user->save();
            } else {
                if ($this->distinguishForumType() == 'discuz') {
                    app('db.remote')->where('uid', $user->forum_uid)->update([
                        'email' => $user->email,
                    ]);
                } else {
                    app('db.remote')->where('id', $user->forum_uid)->update([
                        'email' => $user->email,
                    ]);
                }
            }
        }

        $player = Player::where('uid', $user->uid)->first();
        //如果用户没有角色，则不进行同步
        if (!$player) {
            return;
        }
        $player_name = $player->name;
        // 同理，保证两边的用户名、绑定角色名一致。
        if (
            $player_name != $remoteUser->username &&
            !empty($player_name) &&
            !empty($remoteUser->username)
        ) {
            if (option('forum_duplicated_prefer') == 'remote') {
                $player_name = $remoteUser->username;
                $user->save();
            } else {
                app('db.remote')->where('email', $user->email)->update([
                    'username' => $player_name,
                ]);
            }
        }

        // 如果使用加盐算法,就不同步密码了
        if (config('secure.cipher') == 'SALTED2MD5') {
            return;
        }
        // 如果两边用户的密码或 salt 不同，就按照「重复处理」选项的定义来处理。
        if (
            $user->password != $remoteUser->password ||
            (!empty($remoteUser->salt) && $user->salt != $remoteUser->salt)
        ) {
            if (option('forum_duplicated_prefer') == 'remote') {
                $user->password = $remoteUser->password;
                if (stristr(get_class(app('cipher')), 'SALTED')) {
                    $user->salt = $remoteUser->salt;
                }
                $user->save();
            } else {
                app('db.remote')->where('email', $user->email)->update(array_merge(
                    ['password' => $user->password],
                    stristr(get_class(app('cipher')), 'SALTED')
                        ? ['salt' => $user->salt]
                        : []
                ));
            }
        }
    }

    /**
     * 判断论坛类型.
     *
     * @return string|null
     */
    protected function distinguishForumType()
    {
        if (config('secure.cipher') == 'SALTED2MD5') {
            //discuz只会使用SALTED2MD5加密
            return 'discuz';
        } elseif (config('secure.cipher') == 'BCRYPT' || config('secure.cipher') == 'PHP_PASSWORD_HASH') {
            //Flarum会使用这两种算法
            return 'flarum';
        }
    }

    /**
     * 同步所给的皮肤站用户至论坛数据库。
     *
     * @return stdClass|void
     */
    protected function syncFromLocal(User $user)
    {
        $player = Player::where('uid', $user->uid)->first();
        //如果用户没有角色，则不进行同步
        if (!$player) {
            return;
        }
        $player_name = $player->name;
        //如果论坛数据库里有邮箱和用户名都相同的账户，则将其绑定至本皮肤站账户
        $remoteDB = app('db.remote');
        $remoteUser = $remoteDB->where([
            ['username', $player_name],
            ['email', $user->email],
        ])->first();
        if ($remoteUser) {
            if ($this->distinguishForumType() == 'discuz') {
                $user->forum_uid = $remoteUser->uid;
            } else {
                $user->forum_uid = $remoteUser->id;
            }
            $user->save();

            return $remoteUser;
        }
        if (config('secure.cipher') == 'BCRYPT' || config('secure.cipher') == 'PHP_PASSWORD_HASH') {
            // 用这个加密算法说明正在使用 Flarum
            app('db.remote')->insertGetId([
                'username' => $player_name ?? $user->nickname,
                'email' => $user->email,
                'password' => $user->password,
                'is_email_confirmed' => (int) $user->verified,
                'joined_at' => $user->register_at,
            ]);
        } elseif (config('secure.cipher') == 'SALTED2MD5') {
            // 用这个加密算法说明正在使用 Discuz! 或 PhpWind
            app('db.remote')->insertGetId([
                'username' => $player_name,
                'email' => $user->email,
                'password' => 'defaultPassword',
                'regip' => $user->ip,
                'regdate' => time(),
                'salt' => forum_generate_random_salt(),
            ]);
        }

        return app('db.remote')->where('email', $user->email)->first();
    }

    /**
     * 从论坛数据库查找符合条件的用户数据记录，并同步至皮肤站数据库。
     *
     * @param string $column 用于查找的字段名，username 或者 email
     * @param string $value  用于查找的字段的记录值
     *
     * @return User|void
     */
    protected function syncFromRemote($column, $value)
    {
        // 从论坛数据库查找
        $result = app('db.remote')->where($column, $value)->first();

        if (!$result) {
            return;
        }

        //如果皮肤站数据库中已经存在对应uid的账户，则直接返回该账户
        $forumId = null;
        if ($this->distinguishForumType() == 'discuz') {
            $forumId = $result->uid;
        } else {
            $forumId = $result->id;
        }
        $user = User::where('forum_uid', $forumId)->first();
        if ($user) {
            return $user;
        }

        // 在皮肤站数据库新建用户及角色
        $user = new User();
        $user->email = $result->email;
        $user->password = $result->password;
        $user->ip = $result->regip ?? '255.255.255.255';
        $user->score = option('user_initial_score');
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();
        $user->permission = User::NORMAL;
        $user->nickname = $result->username;
        $user->verified = boolval($result->is_email_confirmed ?? false);
        if ($this->distinguishForumType() == 'discuz') {
            $user->forum_uid = $result->uid;
        } else {
            $user->forum_uid = $result->id;
        }
        $user->save();
        event(new Events\UserRegistered($user));

        $user->refresh();
        if ($player = Player::where('name', $result->username)->first()) {
            // 保证角色为该用户所有
            $player->uid = $user->uid;
            $player->save();
        } else {
            $player = new Player();
            $player->uid = $user->uid;
            $player->name = $result->username;
            $player->tid_skin = 0;
            $player->tid_cape = 0;
            $player->last_modified = Carbon::now();
            $player->save();
            event(new Events\PlayerWasAdded($player));
        }

        return $user;
    }
}
