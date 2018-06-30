<?php

namespace SinglePlayerLimit\Controllers;

use Validator;
use App\Models\User;
use App\Http\Controllers\AdminController as BaseController;

class AdminController extends BaseController
{
    public function queryByUid()
    {
        $uid = request()->get('uid');

        if (! $uid) {
            return json('UID 无效', 1);
        }

        $user = User::find($uid);

        if (! $user) {
            return json('用户不存在', 2);
        }

        if (! $user->player_name) {
            return json('该用户尚未绑定角色名', 3);
        }

        return json("用户 [UID=$uid] 绑定的角色名为 [$user->player_name]", 0);
    }

    public function queryByPlayerName()
    {
        $playerName = request()->get('playerName');

        if (! $playerName) {
            return json('角色名无效', 1);
        }

        $user = User::where('player_name', $playerName)->first();

        if (! $user) {
            return json('没有用户绑定过这个角色名', 2);
        }

        return json("角色名 [$playerName] 绑定的用户为 [UID=$user->uid]", 0);
    }

    public function changeUserBindPlayerName()
    {
        $uid = request()->get('uid');
        $newPlayerName = request()->get('newPlayerName');

        $this->validate(request(), [
            'uid' => 'required',
            'newPlayerName' => get_player_name_validation_rules()
        ]);

        $user = User::find($uid);

        if (! $user) {
            return json('用户不存在', 2);
        }

        $originalOwner = User::where('player_name', $newPlayerName)->first();
        // 迫真 NTR
        if ($originalOwner) {
            $originalOwner->player_name = '';
            $originalOwner->save();
        }

        $user->player_name = $newPlayerName;
        $user->save();

        return json("用户 [uid=$uid] 绑定的角色名已被修改为 [$newPlayerName]", 0);
    }
}
