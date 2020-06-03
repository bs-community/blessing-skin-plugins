<?php

namespace Integration\Forum\Listener;

use App\Models\User;
use App\Models\Player;
use App\Events\UserTryToLogin;
use Blessing\Filter;
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
            if (! $user) return;

            $password = request('password');

            // 如果用户的密码还是用 .env 里的那个固定 salt 计算的 hash
            // 就生成个随机 salt 放到 users 表里去
            if ($user->password == app('cipher')->hash($password, config('secure.salt'))) {
                $user->salt = forum_generate_random_salt();
                $user->password = app('cipher')->hash($password, $user->salt);
                $user->save();
            }
        });

        resolve(Filter::class)->add('verify_password', function ($passed, $raw, $user) {
            if (!$user->salt) {
                $user->salt = forum_generate_random_salt();
                $user->save();
            }

            $hashed = app('cipher')->hash($raw, $user->salt);

            return hash_equals($user->password, $hashed);
        });
    }
}
