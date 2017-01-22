<?php
/**
 * @Author: printempw
 * @Date:   2016-10-30 13:03:01
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 10:28:14
 */

namespace DataIntegration\Synchronizer;

use Utils;
use App\Models\User;
use App\Models\Player;
use DataIntegration\Log;

class LoginSystemSynchronizer extends BaseSynchronizer
{
    public function syncFromTarget($username)
    {
        $result = app('db.target')->select($this->columns['username'], $username);

        $user = new User;

        $user->email        = '';
        $user->password     = $result[$this->columns['password']];
        $user->ip           = $result[$this->columns['ip']];
        $user->score        = option('user_initial_score') - option('score_per_player');
        $user->register_at  = Utils::getTimeFormatted();
        $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
        $user->permission   = User::NORMAL;
        $user->username     = $username;
        $user->nickname     = $username;
        $user->save();

        $player = new Player;

        $player->uid           = $user->uid;
        $player->player_name   = $username;
        $player->preference    = "default";
        $player->last_modified = Utils::getTimeFormatted();
        $player->save();

        event(new \App\Events\PlayerWasAdded($player));

        Log::info("[DataIntegration][$username] Register a new user on skin server.");
    }

    public function syncFromSelf($username)
    {
        $result = app('users')->get($username, 'username');

        app('db.target')->insert([
            $this->columns['username'] => $username,
            $this->columns['password'] => $result->password,
            $this->columns['ip']       => $result->ip
        ]);

        Log::info("[DataIntegration][$username] Add a new user to target database.");
    }

}
