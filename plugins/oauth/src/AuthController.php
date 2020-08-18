<?php

namespace Blessing\OAuthCore;

use App\Models\User;
use Blessing\Filter;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Vectorface\Whip\Whip;

class AuthController extends Controller
{
    public function login($driver)
    {
        return Socialite::driver($driver)->redirect();
    }

    public function callback(Dispatcher $dispatcher, Filter $filter, $driver)
    {
        $remoteUser = Socialite::driver($driver)->user();

        $email = $remoteUser->email;
        if (empty($email)) {
            abort(500, 'Unsupported OAuth Server which does not provide email.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $whip = new Whip();
            $ip = $whip->getValidIpAddress();
            $ip = $filter->apply('client_ip', $ip);

            $user = new User();
            $user->email = $email;
            $user->nickname = $remoteUser->nickname ?? $remoteUser->name ?? $email;
            $user->score = option('user_initial_score');
            $user->avatar = 0;
            $user->password = '';
            $user->ip = $ip;
            $user->permission = User::NORMAL;
            $user->register_at = Carbon::now();
            $user->last_sign_at = Carbon::now()->subDay();
            $user->verified = true;

            $user->save();
            $dispatcher->dispatch('auth.registration.completed', [$user]);
        }

        $dispatcher->dispatch('auth.login.ready', [$user]);
        Auth::login($user);
        $dispatcher->dispatch('auth.login.succeeded', [$user]);

        return redirect('/user');
    }
}
