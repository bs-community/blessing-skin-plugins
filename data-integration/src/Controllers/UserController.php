<?php

namespace DataIntegration\Controllers;

use DataIntegration\Utils as MyUtils;
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

        return view('DataIntegration::user')->with([
            'user' => $user,
            'storage' => $storage,
            'player' => MyUtils::getUniquePlayer(app('user.current'))
        ]);
    }

    public function closet()
    {
        return view('DataIntegration::closet', [
            'user' => app('user.current'),
            'player' => MyUtils::getUniquePlayer(app('user.current'))
        ]);
    }

}
