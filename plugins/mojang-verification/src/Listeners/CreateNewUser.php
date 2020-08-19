<?php

namespace GPlane\Mojang\Listeners;

use App\Models\User;
use Blessing\Filter;
use Carbon\Carbon;
use GPlane\Mojang\AccountService;
use GPlane\Mojang\MojangVerification;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Vectorface\Whip\Whip;

class CreateNewUser
{
    /** @var Filter */
    protected $filter;

    /** @var AccountService */
    protected $accountService;

    /** @var Dispatcher */
    protected $events;

    public function __construct(
        Filter $filter,
        AccountService $accountService,
        Dispatcher $dispatcher
    ) {
        $this->filter = $filter;
        $this->accountService = $accountService;
        $this->events = $dispatcher;
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

        $result = $this->accountService->validate($email, $password);
        if (!$result['valid']) {
            return;
        }

        $uuid = Arr::get($result['selected'], 'id');
        $record = MojangVerification::where('uuid', $uuid)->first();
        if ($record) {
            $user = User::find($record->user_id);
            if ($user) {
                $this->events->dispatch('user.profile.updating', [$user, 'email', ['email' => $email]]);
                $user->update(['email' => $email]);
                $this->events->dispatch('user.profile.updated', [$user, 'email', ['email' => $email]]);

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

        $this->events->dispatch('auth.registration.completed', [$user]);

        $this->accountService->bindAccount($user, $result['profiles'], $result['selected']);
    }
}
