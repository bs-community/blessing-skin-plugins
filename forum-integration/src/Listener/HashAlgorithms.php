<?php

namespace Integration\Forum\Listener;

use App\Events\EncryptUserPassword;
use App\Events\UserTryToLogin;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

class HashAlgorithms
{
    /**
     * @param Dispatcher $events
     */
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
            $user = User::where(
                $event->authType == 'email' ? 'email' : 'player_name',
                $event->identification
            )->first();
            if (!$user) {
                return;
            }

            $password = request('password');

            // 如果用户的密码还是用 .env 里的那个固定 salt 计算的 hash
            // 就生成个随机 salt 放到 users 表里去
            if ($user->password == app('cipher')->hash($password, config('secure.salt'))) {
                $user->salt = forum_generate_random_salt();
                $user->password = app('cipher')->hash($password, $user->salt);
                $user->save();
            }
        });

        $events->listen(EncryptUserPassword::class, function ($event) {
            $user = $event->user;

            // 生成并保存随机 salt
            if (!$user->salt) {
                $user->salt = forum_generate_random_salt();
                $user->save();
            }

            return app('cipher')->hash($event->rawPasswd, $user->salt);
        });
    }
}
