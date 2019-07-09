<?php

use App\Events;
use GPlane\Mojang;
use App\Models\User;
use App\Services\Hook;
use App\Models\Player;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

return function (Dispatcher $events) {
    $events->listen(Events\UserTryToLogin::class, function ($payload) {
        if ($payload->authType != 'email') {
            return;
        }
        $user = User::where('email', $payload->identification)->first();
        if ($user) {
            return;
        }

        $result = validate_mojang_account($payload->identification, request('password'));
        if (! $result['valid']) {
            return;
        }

        $uuid = Arr::get($result['selected'], 'id');
        $record = Mojang\MojangVerification::where('uuid', $uuid)->first();
        if ($record) {
            $user = User::find($record->user_id);
            if ($user) {
                $user->update(['email' => $payload->identification]);
                event(new Events\UserProfileUpdated('email', $user));
                return;
            }
        }

        $user = new User;
        $user->email = $payload->identification;
        $user->nickname = Arr::get($result['selected'], 'name', '');
        $user->score = option('user_initial_score');
        $user->avatar = 0;
        $user->password = User::getEncryptedPwdFromEvent(request('password'), $user)
            ?: app('cipher')->hash(request('password'), config('secure.salt'));
        $user->ip = get_client_ip();
        $user->permission = User::NORMAL;
        $user->register_at = get_datetime_string();
        $user->last_sign_at = get_datetime_string(time() - 86400);
        $user->save();

        event(new Events\UserRegistered($user));

        bind_mojang_account($user, $result['profiles'], $result['selected']);
    });

    $events->listen(Illuminate\Auth\Events\Authenticated::class, function ($payload) {
        $uid = $payload->user->uid;
        if (Mojang\MojangVerification::where('user_id', $uid)->count() == 1) {
            Hook::addUserBadge('正版', 'purple');
        } else {
            Hook::addScriptFileToPage(
                plugin_assets('mojang-verification', 'bind.js'),
                ['user']
            );
        }
    });

    Hook::addStyleFileToPage(
        plugin_assets('mojang-verification', 'registrar-notice.css'),
        ['auth/register']
    );

    Hook::addRoute(function ($router) {
        $router->get('/verify-mojang', function () {
            return json('', 0, ['score' => option('mojang_verification_score_award', 0)]);
        })->middleware(['web']);

        $router->post('/verify-mojang', function () {
            $user = auth()->user();
            $result = validate_mojang_account($user->email, request('password'));
            if ($result['valid']) {
                bind_mojang_account($user, $result['profiles'], $result['selected']);
            }
            return redirect('/user');
        })->middleware(['web', 'auth']);
    });
};
