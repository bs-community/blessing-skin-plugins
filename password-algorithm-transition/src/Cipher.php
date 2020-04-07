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
        $result = false;
        foreach (app()->tagged('ciphers') as $cipher) {
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
            Event::listen('auth.login.succeeded', function ($user) use ($password) {
                $user->changePassword($password);
            });
        }

        return $result;
    }
}
