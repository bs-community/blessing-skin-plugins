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
//            Events\PlayerProfileUpdated::class//bysmyhw
        ], [$this, 'synchronize']);
    }

    public function synchronize(Events\Event $event)
    {
        //如果用户更改了邮箱,同步到论坛
        //S
        if($event instanceof Events\UserProfileUpdated && $event->type == 'email' )
        {
            $tempPlayer = Player::where('uid', $event->user->uid)->first();
            if($tempPlayer!=null)
            {
                $this->syncEmailFromLocal($tempPlayer->name);
            }
        }
        //如果用户尝试登入,从这里开始触发
        if ($event instanceof Events\UserTryToLogin) 
        {
            if ($event->authType == 'email') //如果是邮箱登入
            {
                $user = User::where('email', $event->identification)->first();//查询对应用户
                if($user)
                {
                    $tempPlayer = Player::where('uid', $user->uid)->first();
                    if($tempPlayer)
                    {
                        $this->syncEmailFromLocal($tempPlayer->name);//根据查询处的用户获得角色并获取角色名,并尝试同步邮箱
                    }
                }
            } 
            else //如果是角色名登入
            {
                $this->syncEmailFromLocal($event->identification);//直接根据输入的角色名尝试同步邮箱
                $player = Player::where('name', $event->identification)->first();//查询对应角色
                $user = optional($player, function ($p) {return $p->user;});//根据角色查询对应用户
            }

            // 如果正在登录的用户在皮肤站数据库中不存在，就尝试从论坛数据库同步过来
            if (! $user) 
            {
                $user = $this->syncFromRemote($event->authType, $event->identification);
            }
        } 
        else
        {
            $user = $event->user;
        }

        // 如果到这里了皮肤站里还是没有这个用户，就说明该用户确实是不存在的
        if (! $user) return;

        if(Player::where('uid', $user->uid)->first()==null) return;//如果查不到,返回,防止下一句出错
        $remoteUser = app('db.remote')->where('username', Player::where('uid', $user->uid)->first()->name)->first();
        // 如果这个角色存在于皮肤站，却不存在与论坛数据库中的话，就尝试同步过去
        if (! $remoteUser) 
        {
            $remoteUser = $this->syncFromLocal($user);
        }

        // 如果到这里论坛数据库里还是没有这个用户，就说明同步失败了，那下面的逻辑也不用执行了。
        if (! $remoteUser) return;

        // 如果两边用户的密码或 salt 不同，就按照「重复处理」选项的定义来处理。
        if ($user->password != $remoteUser->password || (! empty($remoteUser->salt) && $user->salt != $remoteUser->salt) ) 
        {
                if (option('forum_duplicated_prefer') == 'remote') 
                {
                    $user->password = $remoteUser->password;
                    if (stristr(get_class(app('cipher')), 'SALTED')) 
                    {
                        $user->salt = $remoteUser->salt;
                    }
                    $user->save();
                } 
                else 
                {
                    app('db.remote')->where('email', $user->email)->update(array_merge(['password' => $user->password],stristr(get_class(app('cipher')), 'SALTED')? ['salt' => $user->salt]: []));
                }
        }

        // 同理，保证两边的用户名、绑定角色名一致。
        if (
            $user->player_name != $remoteUser->username &&
            ! empty($user->player_name) &&
            ! empty($remoteUser->username)
            ) 
        {
            if (option('forum_duplicated_prefer') == 'remote') 
            {
                $user->player_name = $remoteUser->username;
                $user->save();
            }
            else 
            {
                app('db.remote')->where('email', $user->email)->update(['username' => $user->player_name]);
            }
        }
    }

    //将用户的email同步至论坛
    protected function syncEmailFromLocal($username)
    {
        $bbs = app('db.remote')->where('username', $username)->first();//获取论坛里该用户名的邮箱
        $mcskin = User::where('username', $username)->first();//或许皮肤站里该用户名的邮箱
        if($bbs==null || $mcskin==null){return;}//如果有任意一端的数据不存在(即用户不存在),跳过
//        if($bbs->password == $mcskin->password && $bbs->salt == $mcskin->salt)
        if($bbs->email == $mcskin->email){return;}//如果email本就相同,跳过
        {app('db.remote')->where('username', $username)->update(['email' => $mcskin->email]);}
    }

    /**
     * 同步所给的皮肤站用户至论坛数据库。
     *
     * @param User $user
     * @return stdClass|void
     */
    protected function syncFromLocal(User $user)
    {
        $tempPlayer = Player::where('uid', $user->uid)->first();
        if(!$tempPlayer){return;}//如果查不到这个玩家,就直接返回
        $realName=$tempPlayer->name;
        if (config('secure.cipher') == 'BCRYPT' || config('secure.cipher') == 'PHP_PASSWORD_HASH') {
            // 用这个加密算法说明正在使用 Flarum
            app('db.remote')->insertGetId([
                'username' => $user->player_name ?? $realName,
                'email'    => $user->email,
                'password' => $user->password,
                'is_email_confirmed' => (int) $user->verified,
                'joined_at' => $user->register_at,
            ]);
        } elseif (config('secure.cipher') == 'SALTED2MD5') {
            // 用这个加密算法说明正在使用 Discuz! 或 PhpWind
            app('db.remote')->insertGetId([
                'username' => $realName,
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
        $user = new User;
        $user->email = $result->email;
        $user->password = $result->password;
        $user->ip = $result->regip ?? '255.255.255.255';
        $user->score = option('user_initial_score');
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();
        $user->permission = User::NORMAL;
        $user->nickname = $result->username;
        $user->player_name = $result->username;
        $user->verified = boolval($result->is_email_confirmed ?? false);
        if (stristr(get_class(app('cipher')), 'SALTED')) {
            $user->salt = $result->salt ?? '';
        }
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
            $player->last_modified = Carbon::now();
            $player->save();
            event(new Events\PlayerWasAdded($player));
        }

        return $user;
    }
}
