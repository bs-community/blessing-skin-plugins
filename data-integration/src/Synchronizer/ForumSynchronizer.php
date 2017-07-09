<?php
/**
 * @Author: printempw
 * @Date:   2016-10-30 13:03:01
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 09:58:29
 */

namespace DataIntegration\Synchronizer;

use DB;
use Utils;
use App\Models\User;
use App\Models\Player;
use DataIntegration\Log;
use DataIntegration\Utils as MyUtils;

class ForumSynchronizer extends BaseSynchronizer
{
    public function syncFromTarget($username)
    {
        $result = app('db.target')->select($this->columns['username'], $username);

        // 如果皮肤站数据库中存在邮箱相同的用户
        if ($user = User::where('email', $result['email'])->first()) {
            // 根据「重复处理」选择更新皮肤站/目标数据库中的用户名
            if (option('da_duplicated_prefer') == 'skin') {
                $this->updateTargetUsername($user, $user->username);
            } else {
                $this->updateSelfUsername($user, $username);

                if ($correspondingPlayer = Player::where('player_name', $username)->first()) {
                    if ($correspondingPlayer->uid = $user->uid) {
                        return;
                    } else {
                        $correspondingPlayer->uid = $user->uid;
                        $correspondingPlayer->save();
                    }
                } else {
                    // Add a new player
                    MyUtils::getUniquePlayer($user);
                }

            }

            return;
        }

        $user = new User;

        $user->email        = $result['email'];
        $user->password     = $result[$this->columns['password']];
        $user->ip           = $result[$this->columns['ip']];
        $user->score        = option('user_initial_score');
        $user->register_at  = Utils::getTimeFormatted();
        $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
        $user->permission   = User::NORMAL;
        $user->nickname     = $username;
        $user->save();

        $player = new Player;

        $player->uid           = $user->uid;
        $player->player_name   = $username;
        $player->preference    = "default";
        $player->last_modified = Utils::getTimeFormatted();
        $player->save();

        Log::info("[DataIntegration][$username] Register new user with email [{$result['email']}].");

        event(new \App\Events\PlayerWasAdded($player));
    }

    public function syncFromSelf($username)
    {
        $user = app('users')->get($username, 'username');

        // 如果目标数据库里有相同邮箱的用户
        if (app('db.target')->has('email', $user->email)) {
            // 根据「重复处理」选择更新皮肤站/目标数据库中的用户名
            if (option('da_duplicated_prefer') == 'skin') {
                $this->updateTargetUsername($user, $user->username);
            } else {
                $this->updateSelfUsername($user, MyUtils::getUsernameFromTargetByEmail($user->email));
            }
            return;
        }

        // 只有 Discuz 和 Phpwind 有 email 和 salt 这俩字段
        app('db.target')->insert([
            $this->columns['username'] => $username,
            'email'                    => $user->email,
            $this->columns['password'] => $user->password,
            $this->columns['ip']       => $user->ip,
            'salt'                     => config('secure.salt')
        ]);

        Log::info("[DataIntegration][$username] Add a new user with email [$user->email] and salt [".config('secure.salt')."] to target database");
    }

    public function syncPassword($username)
    {
        $uid = DB::table('players')->where('player_name', $username)->first()->uid;

        $email_skin   = app('db.self')->where('uid', $uid)->first()->email;
        $email_target = app('db.target')->select($this->columns['username'], $username)['email'];

        if ($email_skin != $email_target) {
            if (option('da_duplicated_prefer') == 'target') {
                // update email on skin server
                app('db.self')->where('uid', $uid)->update(['email' => $email_target]);

                Log::info("[DataIntegration][$username] Update email on skin server to [$email_target]");
            } else {
                app('db.target')->update('email', $email_skin, ['where' => "{$this->columns['username']}='$username'"]);

                Log::info("[DataIntegration][$username] Update email in target database to [$email_skin]");
            }
        }

        parent::syncPassword($username);
    }

    protected function updateSelfUsername(User $user, $username)
    {
        $user->username = $username;
        $user->nickname = $username;
        $user->save();

        Log::info("[DataIntegration][$user->email] The username in skin db has been set to [$username]");
    }

    protected function updateTargetUsername(User $user, $username)
    {
        app('db.target')->update('username', $username, ['where' => 'email="'.$user->email.'"']);

        Log::info("[DataIntegration][$user->email] The username in target db has been set to [$username]");
    }

}
