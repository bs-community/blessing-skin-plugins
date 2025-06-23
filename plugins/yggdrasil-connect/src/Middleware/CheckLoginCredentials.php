<?php

namespace LittleSkin\YggdrasilConnect\Middleware;

use App\Models\User as BaseUser;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\ForbiddenOperationException;
use LittleSkin\YggdrasilConnect\Exceptions\Yggdrasil\IllegalArgumentException;
use LittleSkin\YggdrasilConnect\Models\User;

class CheckLoginCredentials
{
    public function handle(Request $request, \Closure $next)
    {
        $validation = Validator::make($request->all(), [
            'clientToken' => ['nullable', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'requestUser' => ['nullable', 'boolean'],
        ]);

        if ($validation->fails()) {
            if ($validation->errors()->has('username') || $validation->errors()->has('password')) {
                throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::exceptions.auth.empty'));
            }
            throw new IllegalArgumentException(trans('LittleSkin\\YggdrasilConnect::exceptions.illegal'));
        }

        $identification = $request->input('username');
        $password = $request->input('password');

        Log::channel('ygg')->info("User [$identification] is try to authenticate with", [$request->except(['username', 'password'])]);

        if (filter_var($identification, FILTER_VALIDATE_EMAIL)) {
            /** @var User */
            $user = User::where('email', $identification)->first();
        } else {
            $player = Player::where('name', $identification)->first();
            /** @var BaseUser */
            $user = optional($player)->user;
        }

        try {
            if (empty($user)) {
                throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.auth.not-match'));
            }

            if (!$user->verifyPassword($password)) {
                throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.auth.not-match'));
            }

            if (!empty($user->locale)) {
                app()->setLocale($user->locale);
            }

            if ($user->permission == User::BANNED) {
                throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.user.banned'));
            }

            if (option('require_verification') && $user->verified === false) {
                throw new ForbiddenOperationException(trans('LittleSkin\\YggdrasilConnect::exceptions.user.not-verified'));
            }
        } catch (ForbiddenOperationException $e) {
            Log::channel('ygg')->info("User [$identification] authentication failed.", [$e->getMessage()]);
            if ($request->is('api/yggdrasil/authserver/signout')) { // signout 不管成功与否都返回 204
                return response()->noContent();
            }
            throw $e;
        }

        Auth::setUser($user);
        return $next($request);
    }
}
