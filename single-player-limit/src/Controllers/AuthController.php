<?php

namespace SinglePlayerLimit\Controllers;

use Utils;
use App\Events;
use App\Models\User;
use App\Models\Player;
use Illuminate\Http\Request;
use App\Services\Repositories\UserRepository;
use App\Http\Controllers\AuthController as BaseController;

class AuthController extends BaseController
{
    public function register()
    {
        if (option('user_can_register')) {
            return view('SinglePlayerLimit::register');
        } else {
            throw new PrettyPageException(trans('auth.register.close'), 7);
        }
    }

    public function handleRegister(Request $request, UserRepository $users)
    {
        if (! $this->checkCaptcha($request))
            return json(trans('auth.validation.captcha'), 1);

        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|min:8|max:32',
            'playerName' => 'required|'.(option('allow_chinese_playername') ? 'pname_chinese' : 'playername')
        ]);

        if (! option('user_can_register')) {
            return json(trans('auth.register.close'), 7);
        }

        $playerName = $request->get('playerName');

        event(new Events\CheckPlayerExists($playerName));

        if (Player::where('player_name', $playerName)->first()) {
            return json('此角色名已被占用', 2);
        }

        // If amount of registered accounts of IP is more than allowed amounts,
        // then reject the register.
        if (User::where('ip', Utils::getClientIp())->count() < option('regs_per_ip'))
        {
            // Register a new user.
            // If the email is already registered,
            // it will return a false value.
            $user = User::register(
                $request->input('email'),
                $request->input('password'), function($user) use ($request)
            {
                $user->ip           = Utils::getClientIp();
                $user->score        = option('user_initial_score');
                $user->register_at  = Utils::getTimeFormatted();
                $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
                $user->permission   = User::NORMAL;
                $user->nickname     = $request->input('playerName');
                // 同时填写本插件添加至 users 的字段
                $user->player_name  = $request->input('playerName');
            });

            if (! $user) {
                return json(trans('auth.register.registered'), 5);
            }

            event(new Events\UserRegistered($user));

            return json([
                'errno'    => 0,
                'msg'      => trans('auth.register.success'),
                'token'    => $user->getToken(),
            ]) // Set cookies
            ->withCookie('uid', $user->uid, 60)
            ->withCookie('token', $user->getToken(), 60);

        } else {
            return json(trans('auth.register.max', ['regs' => option('regs_per_ip')]), 7);
        }
    }
}
