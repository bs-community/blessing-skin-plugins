<?php
/**
 * @Author: printempw
 * @Date:   2017-01-06 22:05:19
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 10:14:47
 */

namespace DataIntegration\Controllers;

use DB;
use Event;
use Utils;
use App\Events;
use App\Models\User;
use App\Models\Player;
use DataIntegration\Log;
use Illuminate\Http\Request;
use App\Events\CheckPlayerExists;
use DataIntegration\Utils as MyUtils;
use App\Services\Repositories\UserRepository;
use App\Http\Controllers\AuthController as BaseController;

class AuthController extends BaseController
{
    public function handleRegister(Request $request, UserRepository $users)
    {
        if (!$this->checkCaptcha($request))
            return json(trans('auth.validation.captcha'), 1);

        $this->validate($request, [
            'email'       => 'required|email',
            'password'    => 'required|min:8|max:16',
            'player_name' => 'required|'.(option('allow_chinese_playername') ? 'pname_chinese' : 'playername')
        ]);

        if (!option('user_can_register')) {
            return json(trans('auth.register.close'), 7);
        }

        // player name is regarded as unique username when data integration is enabled
        $username = $request->input('player_name');

        Event::fire(new CheckPlayerExists($username));

        if (Player::where('player_name', $username)->first())
            return json(trans('user.player.add.repeated'), 6);

        // If amount of registered accounts of IP is more than allowed amounts,
        // then reject the register.
        if (User::where('ip', Utils::getClientIp())->count() < option('regs_per_ip'))
        {
            // Register a new user.
            // If the email is already registered,
            // it will return a false value.
            $user = User::register(
                $request->input('email'),
                $request->input('password'), function($user) use ($username)
            {
                $user->ip           = Utils::getClientIp();
                $user->score        = option('user_initial_score');
                $user->register_at  = Utils::getTimeFormatted();
                $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
                $user->permission   = User::NORMAL;
                $user->nickname     = $username;
                // username filed is added by plugin
                $user->username     = $username;
            });

            if (!$user) {
                return json(trans('auth.register.registered'), 5);
            }

            $player = MyUtils::addUniquePlayer($user);

            Log::info("[DataIntegration][$username] New user registered.", [
                'email' => $request['email'],
                'player' => $player,
            ]);

            event(new Events\UserRegistered($user));

            return json([
                'errno' => 0,
                'msg'   => trans('auth.register.success'),
                'token' => $user->getToken()
            ]) // set cookies
            ->withCookie('uid', $user->uid, 60)
            ->withCookie('token', $user->getToken(), 60);

        } else {
            return json(trans('auth.register.max', ['regs' => option('regs_per_ip')]), 7);
        }
    }

    public function determineUniqueUsername()
    {
        $user = app('user.current');

        if ($user->username) {
            // ensure the user has a unique player of his uaername
            if ($user->players->count() == 0) {
                return $this->ensureUserHasUniquePlayer($user);
            }

            // delete other players with the same player name
            Player::where('player_name', $user->username)->where('uid', '!=', $user->uid)->delete();
            // delete other players owned by the user
            return Player::where('uid', $user->uid)->where('player_name', '!=', $user->username)->delete();
        }

        if ($user->players->count() == 1) {
            return $this->setUsername($user, $user->players->first()->player_name);
        } else {
            return $this->bindUniqueUsername($user);
        }
    }

    protected function ensureUserHasUniquePlayer(User $user)
    {
        $player = Player::firstOrCreate(['player_name' => $user->username]);
        // format the player
        $player->uid           = $user->uid;
        $player->preference    = "default";
        $player->last_modified = Utils::getTimeFormatted();
        $player->save();

        Log::info("[DataIntegration][$user->username] Unique player created/transfered.");
    }

    protected function bindUniqueUsername(User $user)
    {
        $msg = "";

        if (isset($_POST['username'])) {
            $player = DB::table('players')->where('player_name', $_POST['username'])->first();

            if ($player && $player->uid != $user->uid) {
                $msg = "这个角色名已经被其他人使用啦";
            } else {
                $this->setUsername($user, $_POST['username']);

                MyUtils::addUniquePlayer($user);
                Log::info("[DataIntegration][$user->username] Unique player bound. Trying to sync.");
                event(new Events\UserRegistered($user));

                return true;
            }
        }

        echo view('DataIntegration::bind', compact('user', 'msg'))->render();
        exit;
    }

    protected function setUsername(User $user, $username)
    {
        $user->username = $username;
        $user->nickname = $username;

        return $user->save();
    }

}
