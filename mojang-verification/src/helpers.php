<?php

use App\Events;
use GPlane\Mojang;
use App\Models\User;
use App\Models\Player;
use App\Services\Hook;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Composer\CaBundle\CaBundle;

if (! function_exists('validate_mojang_account')) {

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
                return ['valid' => false];
            }
        } catch (\Exception $e) {
            report($e);
            return ['valid' => false];
        }
    }
}

if (! function_exists('bind_with_mojang_players')) {

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
                        @Mail::to($owner->email)->send(new Mojang\Mail($owner->nickname, $profile['name']));
                        $playerName = $player->name;
                        Hook::sendNotification(
                            [$owner],
                            '角色属主更改通知',
                            '尊敬的 '.$owner->nickname."：\n\n我们很抱歉地告诉您，您的角色 $playerName 已被转让给一个正版用户。\n".
                            "为此，我们向您补偿了 ".option('score_per_player')." 积分。\n\n由此带来的不便，敬请谅解。\n\n\n".
                            'Dear'.$owner->nickname."\n\nWe are sorry to tell you that your player $playerName has been transferred to another user who has paid for Minecraft.\n".
                            "Because of that, we have added ".option('score_per_player')." score to your account.\n\n".
                            'Sorry for the inconvenience.'
                        );
                    }
                }
            } else {
                event(new Events\PlayerWillBeAdded($profile['name']));

                $player = new Player;
                $player->uid = $user->uid;
                $player->name = $profile['name'];
                $player->tid_skin = 0;
                $player->tid_cape = 0;
                $player->save();

                event(new Events\PlayerWasAdded($player));
            }

            // For "yggdrasil-api" plugin.
            if (Schema::hasTable('uuid') && DB::table('uuid')->where('name', $profile['name'])->doesntExist()) {
                DB::table('uuid')->insert(['name' => $profile['name'], 'uuid' => $profile['id']]);
            }
        });
    }
}

if (! function_exists('bind_mojang_account')) {

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
