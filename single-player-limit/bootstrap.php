<?php

use App\Models\User;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;

return function (Filter $filter, Dispatcher $events) {
    $filter->add('can_add_player', function () {
        return new Rejection(trans('SinglePlayerLimit::player.add'));
    });

    $filter->add('can_delete_player', function () {
        return new Rejection(trans('SinglePlayerLimit::player.delete'));
    });

    $filter->add('user_can_edit_profile', function ($can, $action) {
        if ($action === 'nickname') {
            return new Rejection(trans('SinglePlayerLimit::user.nickname'));
        }

        return $can;
    });

    $events->listen('player.renamed', function ($player) {
        /** @var User */
        $user = auth()->user();
        $user->nickname = $player->name;
        $user->save();
    });
};
