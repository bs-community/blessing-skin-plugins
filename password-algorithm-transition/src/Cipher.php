<?php

namespace GPlane\PasswordTransition;

use Event;
use App\Events;
use App\Services\Cipher\BaseCipher;

class Cipher extends BaseCipher
{
    public function hash($value, $salt = '')
    {
        return app('cipher.new')->hash($value, env('SALT'));
    }

    public function verify($password, $hash, $salt = '')
    {
        $attempt = app('cipher.new')->verify($password, $hash, env('SALT'));
        if ($attempt) {
            return true;
        }

        $fallback = app('cipher.old')->verify($password, $hash, env('OLD_SALT'));
        if ($fallback) {
            Event::listen(Events\UserLoggedIn::class, function ($event) use ($password) {
                $event->user->changePassword($password);
                return false;
            });
        }

        return $fallback;
    }
}
