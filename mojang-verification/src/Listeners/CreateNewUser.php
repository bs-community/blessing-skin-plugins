<?php

namespace GPlane\Mojang\Listeners;

use App\Models\User;
use Blessing\Filter;
use Carbon\Carbon;
use Event;
use GPlane\Mojang\MojangVerification;
use Illuminate\Support\Arr;
use Vectorface\Whip\Whip;

require_once __DIR__.'/../helpers.php';

class CreateNewUser
{
    /** @var Filter */
    protected $filter;

    public function __construct(Filter $filter)
    {
        $this->filter = $filter;
    }

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
                Event::dispatch('user.profile.updating', [$user, 'email', ['new_email' => $email]]);
                $user->update(['email' => $email]);
                Event::dispatch('user.profile.updated', [$user, 'email', ['new_email' => $email]]);

                return;
            }
        }

        $whip = new Whip();
        $ip = $whip->getValidIpAddress();
        $ip = $this->filter->apply('client_ip', $ip);

        $user = new User();
        $user->email = $email;
        $user->nickname = Arr::get($result['selected'], 'name', '');
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $password = app('cipher')->hash(request('password'), config('secure.salt'));
        $password = $this->filter->apply('user_password', $password);
        $user->password = $password;
        $user->ip = $ip;
        $user->permission = User::NORMAL;
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();
        $user->save();

        Event::dispatch('auth.registration.completed', [$user]);

        bind_mojang_account($user, $result['profiles'], $result['selected']);
    }
}
