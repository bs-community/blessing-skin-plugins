<?php

namespace SinglePlayerLimit;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class RequireBindPlayer
{
    public function handle(Request $request, Closure $next)
    {
        // This allows us to fetch players list.
        if ($request->is('user/player/list')) {
            return $next($request);
        }

        /** @var User */
        $user = $request->user();
        $count = $user->players()->count();

        if ($request->is('user/player/bind')) {
            if ($count === 1) {
                return redirect('/user');
            } else {
                return $next($request);
            }
        }

        if ($count === 1) {
            return $next($request);
        } else {
            return redirect('user/player/bind');
        }
    }
}
