<?php

use App\Events;
use GPlane\Mojang;
use App\Models\User;
use App\Models\Player;
use App\Services\Hook;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

if (! function_exists('validate_mojang_account')) {

    function validate_mojang_account($username, $password)
    {
        $client = new Client;
        try {
            $response = $client->request('POST', 'https://authserver.mojang.com/authenticate', [
                'json' => array_merge(compact('username', 'password'), [
                    'agent' => ['name' => 'Minecraft', 'version' => 1],
                ]),
            ]);

            if ($response->getStatusCode() == 200) {
                $body = json_decode((string) $response->getBody(), true);
                return [
                    'valid' => Arr::has($body, 'accessToken'),
                    'profiles' => Arr::get($body, 'availableProfiles', []),
                    'selected' => Arr::get($body, 'selectedProfile', []),
                ];
            } else {
                return ['valid' => false];
            }
        } catch (\Exception $e) {
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

                    if (config('mail.driver') != '') {
                        @Mail::to($owner->email)->send(new Mojang\Mail($owner->nickname, $profile['name']));
                        Hook::sendNotification(
                            [$owner],
                            '角色属主更改通知',
                            '尊敬的 '.$owner->nickname."：\n\n我们很抱歉地告诉您，您的角色 $playerName 已被转让给一个正版用户。\n".
                            "为此，我们向您补偿了 ".option('score_per_player')." 积分。\n\n由此带来的不便请谅解。"
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
        $user->score += option('mojang_verification_score_award', 0);
        $user->save();

        $record = new Mojang\MojangVerification;
        $record->user_id = $user->uid;
        $record->uuid = Arr::get($selected, 'id', '');
        $record->verified = true;
        $record->save();

        bind_with_mojang_players($user, $profiles);
    }
}
