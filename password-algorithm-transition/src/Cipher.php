<?php

namespace GPlane\PasswordTransition;

use Event;
use App\Events;
use App\Services\Cipher\BaseCipher;

class Cipher extends BaseCipher
{
    protected $salts;

    public function __construct($salts = [])
    {
        $this->salts = $salts;
    }

    public function hash($value, $salt = '')
    {
        foreach (app()->tagged('ciphers') as $cipher) {
            return $cipher->hash($value, $this->salts[0]);
        }
    }

    public function verify($password, $hash, $salt = '')
    {
        $index = 0;
        $firstCipher = null;
        $result = false;
        foreach (app()->tagged('ciphers') as $cipher) {
            if ($index == 0) {
                $firstCipher = $cipher;
            }

            $isValid = $cipher->verify($password, $hash, $this->salts[$index]);
            if ($isValid) {
                if ($index == 0) {
                    return true;
                } else {
                    $result = $isValid;
                    break;
                }
            }

            $index++;
        }

        if ($result) {
            Event::listen(Events\UserLoggedIn::class, function ($event) use ($password) {
                $event->user->changePassword($password);
                return false;
            });
        }

        return $result;
    }
}
