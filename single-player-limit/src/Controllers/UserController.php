<?php

namespace SinglePlayerLimit\Controllers;

use Log;
use Validator;
use App\Models\Player;
use App\Http\Controllers\AuthController as BaseController;

class UserController extends BaseController
{
    public function index()
    {
        $user = app('user.current');
        $rate = option('score_per_storage');

        $storage['used'] = $user->getStorageUsed();
        $storage['total'] = ($rate == 0) ? 'UNLIMITED' : $storage['used'] + floor($user->getScore() / $rate);
        $storage['percentage'] = $storage['total'] ? $storage['used'] / $storage['total'] * 100 : 100;

        return view('SinglePlayerLimit::user')->with([
            'user' => $user,
            'storage' => $storage,
            'player' => Player::where('player_name', $user->player_name)->first()
        ]);
    }

    public function showBindPage()
    {
        return view('SinglePlayerLimit::bind', ['user' => app('user.current')]);
    }

    public function bindPlayerName()
    {
        $user = app('user.current');
        $playerName = request()->get('playerName');

        if ($user->player_name) {
            return json("您已经绑定了角色名：$user->player_name");
        }

        if (Validator::make(request()->all(), [
            'playerName' => 'required|'.(option('allow_chinese_playername') ? 'pname_chinese' : 'playername')
        ])->fails()) {
            return json('请输入有效的角色名', 1);
        }

        $player = Player::where('player_name', $playerName)->first();

        if ($player && $player->uid != $user->uid) {
            return json('此角色名已被占用', 2);
        } else {
            $user->player_name = $playerName;
            $user->save();

            Log::info("[SinglePlayerLimit] The player name of user [$user->email] has been set to [$playerName]");
        }

        return json("成功绑定了角色名 $playerName", 0);
    }

    public function changePlayerName()
    {
        $user = app('user.current');
        $newPlayerName = request()->get('newPlayerName');

        if (Validator::make(request()->all(), [
            'newPlayerName' => 'required|'.(option('allow_chinese_playername') ? 'pname_chinese' : 'playername')
        ])->fails()) {
            return json('请输入有效的角色名', 1);
        }

        $player = Player::where('player_name', $newPlayerName)->first();

        if ($player && $player->uid != $user->uid) {
            return json('该角色名已被占用', 2);
        } else {
            $user->player_name = $newPlayerName;
            $user->save();

            Log::info("[SinglePlayerLimit] The player name of user [$user->email] has been changed to [$newPlayerName]");
        }

        return json("绑定的角色名成功修改为 $newPlayerName", 0);
    }

    public function closet()
    {
        return view('SinglePlayerLimit::closet', [
            'user' => app('user.current'),
            'player' => Player::where('player_name', app('user.current')->player_name)->first()
        ]);
    }

}
