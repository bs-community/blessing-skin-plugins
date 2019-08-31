<?php

use App\Events;
use GPlane\Mojang;
use App\Models\User;
use App\Services\Hook;
use App\Models\Player;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

return function (Dispatcher $events, User $users) {
    Hook::registerPluginTransScripts('mojang-verification');
    
    $events->listen(Events\UserTryToLogin::class, function ($payload) use ($users) {
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
        $reflection = new ReflectionClass($user);
        if ($reflection->getMethod('getEncryptedPwdFromEvent')->isStatic()) {  // For compatibility with BS v4
            $user->password = User::getEncryptedPwdFromEvent(request('password'), $user)
                ?: app('cipher')->hash(request('password'), config('secure.salt'));
        } else {
            $user->password = $user->getEncryptedPwdFromEvent(request('password'))
                ?: app('cipher')->hash(request('password'), config('secure.salt'));
        }
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
            Hook::addUserBadge(trans('GPlane\Mojang::mojang-verification.pro'), 'purple');
            if (Schema::hasTable('uuid')) {
                Hook::addScriptFileToPage(plugin_assets('mojang-verification', 'update-uuid.js'), ['user/profile']);
            }
        } else {
            Hook::addScriptFileToPage(
                plugin_assets('mojang-verification', 'bind.js'),
                ['user']
            );
        }
    });

    Hook::addScriptFileToPage(
        plugin_assets('mojang-verification', 'register-notice.js'),
        ['auth/register']
    );

    Hook::addRoute(function ($router) {
        $router->get('/mojang/verify', function () {
            return json('', 0, ['score' => option('mojang_verification_score_award', 0)]);
        })->middleware(['web']);

        $router->post('/mojang/verify', function () {
            $user = auth()->user();
            $result = validate_mojang_account($user->email, request('password'));
            if ($result['valid']) {
                bind_mojang_account($user, $result['profiles'], $result['selected']);
                return redirect('/user');
            } else {
                return redirect('/user?mojang=failed');
            }
        })->middleware(['web', 'auth']);

        $router->post('/mojang/update-uuid', function () {
            $uuid = Mojang\MojangVerification::where('user_id', auth()->id())->first()->uuid;
            $client = new GuzzleHttp\Client();
            try {
                $response = $client->request('GET', "https://api.mojang.com/user/profiles/$uuid/names", [
                    'verify' => class_exists('Composer\CaBundle\CaBundle')
                        ? \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
                        : resource_path('misc/ca-bundle.crt'),
                ]);
                $name = json_decode($response->getBody(), true)[0];
                DB::table('uuid')->where('name', $name)->update(['uuid' => $uuid]);
                return json(trans('GPlane\Mojang::mojang-verification.update-success'), 0);
            } catch (Exception $e) {
                return json(trans('GPlane\Mojang::mojang-verification.update-failed'), 1);
            }
        })->middleware(['web', 'auth']);
    });
};
