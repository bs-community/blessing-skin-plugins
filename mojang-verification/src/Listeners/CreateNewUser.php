<?php

namespace GPlane\Mojang\Listeners;

use App\Models\User;
use Blessing\Filter;
use Carbon\Carbon;
use Event;
use Illuminate\Support\Arr;
use GPlane\Mojang\MojangVerification;
use Vectorface\Whip\Whip;

require_once __DIR__.'/../helpers.php';

class CreateNewUser
{
    public function handle($email, $password, $authType)
    {
        if ($authType != 'email') {
            return;
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            return;
        }

        $result = validate_mojang_account($email, $password);
        if (!$result['valid']) {
            return;
        }

        $uuid = Arr::get($result['selected'], 'id');
        $record = MojangVerification::where('uuid', $uuid)->first();
        if ($record) {
            $user = User::find($record->user_id);
            if ($user) {
                $user->update(['email' => $email]);
                event(new \App\Events\UserProfileUpdated('email', $user));
                return;
            }
        }

        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $ip = resolve(Filter::class)->apply('client_ip', $ip);

        $user = new User();
        $user->email = $email;
        $user->nickname = Arr::get($result['selected'], 'name', '');
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $user->password = $user->getEncryptedPwdFromEvent(request('password'))
                ?: app('cipher')->hash(request('password'), config('secure.salt'));
        $user->ip = $ip;
        $user->permission = User::NORMAL;
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();
        $user->save();

        Event::dispatch('auth.registration.completed', [$user]);

        bind_mojang_account($user, $result['profiles'], $result['selected']);
    }
}
