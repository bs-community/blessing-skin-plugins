<?php

namespace RegisterValidation\Listener;

use DB;
use Arr;
use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

class AddValidationRoute
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        Hook::addRoute(function ($router) {
            $router->get('/auth/validate', function() {
                $uid   = Arr::get($_GET, 'uid');
                $token = Arr::get($_GET, 'token');

                if (!$uid || !$token) {
                    abort(403, '非法访问');
                }

                $user = app('users')->get($uid);

                $result = DB::table(RV_TABLE_NAME)->where('uid', $uid)->first();

                if (!$result)
                    abort(403, '无效的链接');

                if ($result->validated == "1")
                    return redirect('/');

                if (time() > strtotime($result->expired_at))
                    abort(403, '链接已过期');

                if ($result->token == $token) {
                    DB::table(RV_TABLE_NAME)->where('uid', $uid)->update(['validated' => '1']);

                    return view('RegisterValidation::success');
                } else {
                    abort(403, '无效的 token');
                }

            })->middleware('web');
        });
    }
}
