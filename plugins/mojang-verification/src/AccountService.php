<?php

namespace GPlane\Mojang;

use App\Models\Player;
use App\Models\User;
use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail as MailService;
use Illuminate\Support\Facades\Schema;
use Log;

class AccountService
{
    /** @var Dispatcher */
    protected $events;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->events = $dispatcher;
    }

    public function bindPlayers(User $user, $profile)
    {
        $player = Player::where('name', $profile->name)->first();

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
                    @MailService::to($owner->email)->send(new Mail($owner, $profile->name));
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
            $this->events->dispatch('player.adding', [$profile->name, $user]);

            $player = new Player();
            $player->uid = $user->uid;
            $player->name = $profile->name;
            $player->tid_skin = 0;
            $player->tid_cape = 0;
            $player->save();

            $this->events->dispatch('player.added', [$player, $user]);
        }

        // For "yggdrasil-api" plugin.
        if (Schema::hasTable('uuid') && DB::table('uuid')->where('name', $profile->name)->doesntExist()) {
            DB::table('uuid')->insert(['name' => $profile->name, 'uuid' => $profile->id]);
        }
    }

    public function bindAccount(User $user, $profile)
    {
        $this->bindPlayers($user, $profile);

        MojangVerification::updateOrCreate(
            ['uuid' => $profile->id],
            ['user_id' => $user->uid, 'verified' => true]
        );

        Log::channel('mojang-verification')->info("User [$user->email] account binded successfully. [name=$profile->name,uuid=$profile->id]");

        $this->events->dispatch('user.mojang-ms.verified', [$user, $profile]);

        $user->score += (int) option('mojang_verification_score_award', 0);
        $user->save();
    }
}
