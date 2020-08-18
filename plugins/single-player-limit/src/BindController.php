<?php

namespace SinglePlayerLimit;

use App\Events\PlayerWasAdded;
use App\Events\PlayerWillBeAdded;
use App\Models\Player;
use App\Rules\PlayerName;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BindController extends Controller
{
    public function bind(Request $request, Dispatcher $dispatcher)
    {
        $name = $request->validate([
            'player' => [
                'required',
                new PlayerName(),
                'min:'.option('player_name_length_min'),
                'max:'.option('player_name_length_max'),
            ],
        ])['player'];
        /** @var User */
        $user = Auth::user();

        $player = Player::where('name', $name)->first();
        if (empty($player)) {
            $dispatcher->dispatch('player.adding', [$name, $user]);
            event(new PlayerWillBeAdded($name));

            $player = new Player();
            $player->uid = $user->uid;
            $player->name = $name;
            $player->tid_skin = 0;
            $player->save();

            $dispatcher->dispatch('player.added', [$player, $user]);
            event(new PlayerWasAdded($player));
        } elseif ($player->uid != $user->uid) {
            return json(trans('user.player.rename.repeated'), 1);
        }

        $user->players()->where('name', '<>', $name)->delete();
        $user->nickname = $name;
        $user->save();

        return json(trans('SinglePlayerLimit::bind.success'), 0);
    }
}
