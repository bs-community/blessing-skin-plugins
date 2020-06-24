<?php

namespace Integration\Forum\Listener;

use App\Events\UserTryToLogin;
use App\Models\Player;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

class HashAlgorithms
{
    public function subscribe(Dispatcher $events)
    {
        if (config('secure.cipher') == 'SALTED2MD5') {
            // 由于皮肤站用的是固定 salt，所以得适配一下
            $this->adaptToDynamicSalt($events);
        }
    }

    protected function adaptToDynamicSalt(Dispatcher $events)
    {
        $events->listen(UserTryToLogin::class, function ($event) {
            if ($event->authType == 'email') {
                $user = User::where('email', $event->identification)->first();
            } else {
                $player = Player::where('name', $event->identification)->first();
                $user = optional($player, function ($p) {
                    return $p->user;
                });
            }
            if (!$user) {
                return;
            }

            $password = request('password');

            // 如果该账户没有绑定论坛账户，就不需要执行.
            if (!$user->forum_uid) {
                return;
            }
            // 如果不是SALTED2MD5算法,就没必要执行下面的代码了.
            if (config('secure.cipher') != 'SALTED2MD5') {
                return;
            }
            $remoteUser = app('db.remote')->where('uid', $user->forum_uid)->first();
            //如果绑定的论坛用户不存在，则不执行
            if (!$remoteUser) {
                return;
            }
            if (option('forum_duplicated_prefer') == 'remote') {
                // 如果密码符合论坛数据，则同步
                if (
                    $remoteUser->password == app('cipher')->hash($password, $remoteUser->salt)
                ) {
                    $user->password = app('cipher')->hash($password, config('secure.salt'));
                    $user->save();
                }
            } else {
                // 如果密码符合皮肤站数据，则同步
                if (
                    $user->password == app('cipher')->hash($password, config('secure.salt'))
                ) {
                    app('db.remote')->where('uid', $remoteUser->uid)->update([
                        'password' => app('cipher')->hash($password, $remoteUser->salt),
                    ]);
                }
            }
        });
    }
}
