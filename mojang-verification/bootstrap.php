<?php

use App\Events;
use Carbon\Carbon;
use GPlane\Mojang;
use App\Models\User;
use App\Services\Hook;
use App\Models\Player;
use Blessing\Filter;
use Vectorface\Whip\Whip;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

return function (Dispatcher $events, Filter $filter) {
    View::composer('GPlane\Mojang::bind', function ($view) {
        $view->with('score', option('mojang_verification_score_award', 0));
    });

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

        $whip = new Whip();
        $ip = $whip->getValidIpAddress();

        $user = new User();
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
        $user->ip = $ip;
        $user->permission = User::NORMAL;
        $user->register_at = Carbon::now();
        $user->last_sign_at = Carbon::now()->subDay();
        $user->save();

        event(new Events\UserRegistered($user));

        bind_mojang_account($user, $result['profiles'], $result['selected']);
    });

    $events->listen(Illuminate\Auth\Events\Authenticated::class, function ($payload) use ($filter) {
        $uid = $payload->user->uid;
        if (Mojang\MojangVerification::where('user_id', $uid)->count() == 1) {
            Hook::addUserBadge(trans('GPlane\Mojang::general.pro'), 'purple');
            if (Schema::hasTable('uuid')) {
                $filter->add('grid:user.profile', function ($grid) {
                    array_unshift($grid['widgets'][0][1], 'GPlane\Mojang::uuid');

                    return $grid;
                });
                Hook::addScriptFileToPage(plugin_assets('mojang-verification', 'update-uuid.js'), ['user/profile']);
            }
        } else {
            $filter->add('grid:user.index', function ($grid) {
                $grid['widgets'][0][1][] = 'GPlane\Mojang::bind';

                return $grid;
            });
        }
    });

    Hook::addScriptFileToPage(
        plugin_assets('mojang-verification', 'register-notice.js'),
        ['auth/register']
    );

    Hook::addRoute(function ($router) {
        $router->post('/mojang/verify', function () {
            $user = auth()->user();
            $result = validate_mojang_account($user->email, request('password'));
            if ($result['valid']) {
                bind_mojang_account($user, $result['profiles'], $result['selected']);
                return back();
            } else {
                return back()->with('mojang-failed', true);
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
                return json(trans('GPlane\Mojang::uuid.success'), 0);
            } catch (Exception $e) {
                return json(trans('GPlane\Mojang::uuid.failed'), 1);
            }
        })->middleware(['web', 'auth']);
    });
};
