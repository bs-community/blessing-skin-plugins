<?php

namespace GPlane\Mojang;

use App\Models\Player;
use App\Models\User;
use App\Services\Hook;
use Composer\CaBundle\CaBundle;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail as MailService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountService
{
    /** @var Dispatcher */
    protected $events;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->events = $dispatcher;
    }

    public function validate(string $username, string $password)
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

    public function bindPlayers(User $user, array $profiles)
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
                        @MailService::to($owner->email)->send(new Mail($owner, $profile['name']));
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
                $this->events->dispatch('player.adding', [$profile['name'], $user]);

                $player = new Player();
                $player->uid = $user->uid;
                $player->name = $profile['name'];
                $player->tid_skin = 0;
                $player->tid_cape = 0;
                $player->save();

                $this->events->dispatch('player.added', [$player, $user]);
            }

            // For "yggdrasil-api" plugin.
            if (Schema::hasTable('uuid') && DB::table('uuid')->where('name', $profile['name'])->doesntExist()) {
                DB::table('uuid')->insert(['name' => $profile['name'], 'uuid' => $profile['id']]);
            }
        });
    }

    public function bindAccount(User $user, array $profiles, $selected)
    {
        $this->bindPlayers($user, $profiles);

        MojangVerification::updateOrCreate(
            ['uuid' => $selected['id']],
            ['user_id' => $user->uid, 'verified' => true]
        );

        $this->events->dispatch('user.mojang.verified', [$user, $selected, $profiles]);

        $user->score += (int) option('mojang_verification_score_award', 0);
        $user->save();
    }
}
