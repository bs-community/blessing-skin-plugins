<?php

namespace Integration\Authme\Listener;

use Schema;
use App\Models\User;
use App\Models\Player;
use App\Events\UserTryToLogin;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;

class HashAlgorithms
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        if (config('secure.cipher') == 'SHA256') {
            app()->singleton('cipher', \Integration\Authme\Cipher\SHA256::class);
            // Authme 的 SHA256 算法和别人不一样
            $this->adaptToAuthmeSha256($events);
        }

        if (config('secure.cipher') == 'SALTED2MD5' || config('secure.cipher') == 'SALTED2SHA512') {
            // 由于皮肤站用的是固定 salt，所以得适配一下
            $this->adaptToDynamicSalt($events);
        }
    }

    protected function adaptToAuthmeSha256(Dispatcher $events)
    {
        app()->singleton('cipher', 'Integration\Authme\Cipher\SHA256');

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

            // 如果用户原来的密码 hash 是直接用 sha256(password) 计算的
            // 就把它更新成 Authme 的 $SHA$salt$sha256(sha256(password).salt) 形式
            if (strlen($user->password) == 64) {
                if (hash('sha256', $password) == $user->password) {
                    $user->password = app('cipher')->hash($password);
                    $user->save();
                }
            }
        });
    }

    protected function adaptToDynamicSalt(Dispatcher $events)
    {
        // 在 users 表上添加 salt 字段
        if (! Schema::hasColumn('users', 'salt')) {
            Schema::table('users', function ($table) {
                $table->string('salt', 6)->default('');
            });
        }

        app()->singleton('cipher', 'Integration\Authme\Cipher\\'.config('secure.cipher'));

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
                $user->salt = app('cipher')->generateSalt();
                $user->password = app('cipher')->hash($password, $user->salt);
                $user->save();
            }
        });

        resolve(Filter::class)->add('verify_password', function ($passed, $raw, $user) {
            if (!$user->salt) {
                $user->salt = app('cipher')->generateSalt();
                $user->save();
            }

            $hashed = app('cipher')->hash($raw, $user->salt);

            return hash_equals($user->password, $hashed);
        });
    }
}
