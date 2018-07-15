<?php

namespace Integration\CrazyLogin\Listener;

use App\Models\User;
use App\Events\UserTryToLogin;
use App\Events\EncryptUserPassword;
use Illuminate\Contracts\Events\Dispatcher;
use Integration\CrazyLogin\Cipher\CrazyCrypt1;

class HashAlgorithms
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        // 用 CRAZYLOGIN_ENCRYPTOR 覆盖 PWD_METHOD 中的设置
        $cipher = menv('CRAZYLOGIN_ENCRYPTOR');
        $className = $cipher == 'CrazyCrypt1' ? CrazyCrypt1::class : "App\Services\Cipher\{$cipher}";

        if (class_exists($className)) {
            app()->instance('cipher.original', app('cipher'));
            app()->singleton('cipher', $className);
        } else {
            abort(500, sprintf(
                '[错误][CrazyLogin 数据对接] 不支持的密码加密方式 < %1$s >，请检查 .env 文件中的 CRAZYLOGIN_ENCRYPTOR 配置项',
            $cipher));
        }

        if ($cipher == 'CrazyCrypt1') {
            $this->adaptToCrazyCrypt1($events);
        }
    }

    protected function adaptToCrazyCrypt1(Dispatcher $events)
    {
        $events->listen(UserTryToLogin::class, function ($event) {
            $user = User::where(
                $event->authType == 'email' ? 'email' : 'player_name',
                $event->identification
            )->first();
            if (! $user) return;

            $password = request('password');
            $salt = config('secure.cipher') == 'CrazyCrypt1' ? $user->player_name : config('secure.salt');

            // 更新密码 hash
            if (app('cipher.original')->verify($password, $user->password, $salt)) {
                $user->password = app('cipher')->hash($password, $user->player_name);
                $user->save();
            }
        });

        $events->listen(EncryptUserPassword::class, function ($event) {
            // CrazyCrypt1 是用 username 作为 salt 的，所以得适配一下
            return app('cipher')->hash($event->rawPasswd, $event->user->player_name);
        });
    }
}
