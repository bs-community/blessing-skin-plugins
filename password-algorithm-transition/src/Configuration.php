<?php

namespace GPlane\PasswordTransition;

use App\Models\User;
use Illuminate\Support\Str;

class Configuration
{
    public function render()
    {
        $users = User::select(['password'])->get();
        $count = User::count();

        $info = $users->groupBy(function ($user) {
            $password = $user->password;
            if (Str::startsWith($password, '$2y')) {
                return 'Bcrypt';
            } elseif (Str::startsWith($password, '$argon2i')) {
                return 'Argon2i';
            } else {
                $length = strlen($password);
                if ($length === 128) {
                    return 'SHA512';
                } elseif ($length === 64) {
                    return 'SHA256';
                } elseif ($length === 32) {
                    return 'MD5';
                }
            }
        })->map(function ($users, $algName) use ($count) {
            $total = count($users);
            $percentage = $total / $count * 100;

            return $algName.': '.$total.' 位用户 ('.$percentage.'%)';
        });

        return view('GPlane\\PasswordTransition::config', compact('info'));
    }
}
