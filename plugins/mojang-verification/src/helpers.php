<?php

use App\Models\Player;
use App\Models\User;
use App\Services\Hook;
use Composer\CaBundle\CaBundle;
use GPlane\Mojang;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

if (!function_exists('validate_mojang_account')) {
    function validate_mojang_account($username, $password)
    {
        try {
            $response = Http::withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->post(
                    'https://authserver.mojang.com/authenticate',
                    array_merge(compact('username', 'password'), [
                        'agent' => ['name' => 'Minecraft', 'version' => 1],
                    ])
                );

            if ($response->ok()) {
                $body = $response->json();

                return [
                    'valid' => Arr::has($body, 'selectedProfile'),
                    'profiles' => $body['availableProfiles'],
                    'selected' => Arr::get($body, 'selectedProfile'),
                ];
            } else {
                Log::warning('Received unexpected HTTP status code from Mojang server: '.$response->status());

                $error = $response->json()['errorMessage'];
                if (Str::contains($error, 'Invalid username or password.')) {
                    $message = trans('GPlane\Mojang::bind.failed.password');
                } elseif ($error === 'Invalid credentials.') {
                    $message = trans('GPlane\Mojang::bind.failed.rate');
                } else {
                    $message = trans('GPlane\Mojang::bind.failed.other');
                }

                return ['valid' => false, 'message' => $message];
            }
        } catch (\Exception $e) {
            report($e);

            return ['valid' => false, 'message' => trans('GPlane\Mojang::bind.failed.other')];
        }
    }
}

if (!function_exists('bind_with_mojang_players')) {
    function bind_with_mojang_players(User $user, $profiles)
    {
        array_walk($profiles, function ($profile) use ($user) {
            $player = Player::where('name', $profile['name'])->first();
            if ($player) {
                if ($player->uid != $user->uid) {
                    $owner = $player->user;

                    $player->uid = $user->uid;
                    $player->tid_skin = 0;
                    $player->tid_cape = 0;
                    $player->save();

                    $owner->score += option('score_per_player');
                    $owner->save();

                    if (config('mail.default') != '') {
                        @Mail::to($owner->email)->send(new Mojang\Mail($owner, $profile['name']));
                        $playerName = $player->name;
                        Hook::sendNotification(
                            [$owner],
                            trans('GPlane\Mojang::bind.notification.title', [], $owner->locale),
                            trans('GPlane\Mojang::bind.notification.content', [
                                'nickname' => $owner->nickname,
                                'player' => $playerName,
                                'score' => option('score_per_player'),
                            ], $owner->locale)
                        );
                    }
                }
            } else {
                /** @var Dispatcher */
                $dispatcher = resolve(Dispatcher::class);
                $dispatcher->dispatch('player.adding', [$profile['name'], $user]);

                $player = new Player();
                $player->uid = $user->uid;
                $player->name = $profile['name'];
                $player->tid_skin = 0;
                $player->tid_cape = 0;
                $player->save();

                $dispatcher->dispatch('player.added', [$player, $user]);
            }

            // For "yggdrasil-api" plugin.
            if (Schema::hasTable('uuid') && DB::table('uuid')->where('name', $profile['name'])->doesntExist()) {
                DB::table('uuid')->insert(['name' => $profile['name'], 'uuid' => $profile['id']]);
            }
        });
    }
}

if (!function_exists('bind_mojang_account')) {
    function bind_mojang_account(User $user, $profiles, $selected)
    {
        bind_with_mojang_players($user, $profiles);

        Mojang\MojangVerification::updateOrCreate(
            ['uuid' => $selected['id']],
            ['user_id' => $user->uid, 'verified' => true]
        );

        $user->score += option('mojang_verification_score_award', 0);
        $user->save();
    }
}
