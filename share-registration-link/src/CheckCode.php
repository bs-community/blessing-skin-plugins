<?php

namespace GPlane\ShareRegistrationLink;

use Event;
use App\Models\User;

class CheckCode
{
    protected $users;

    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function handle($request, \Closure $next)
    {
        $shareCode = $request->input('share_code');
        if (! $shareCode) {
            return $next($request);
        }

        $record = Record::where('code', $shareCode)->first();
        if ($record) {
            $sharer = $this->users->find($record->sharer);
            if ($sharer) {
                $sharer->score += option('reg_link_sharer_score', 50);
                $sharer->save();
            }

            Event::listen(\App\Events\UserRegistered::class, function ($event) {
                $user = $event->user;
                $user->score += option('reg_link_sharee_score', 0);
                $user->save();
            });
        }

        return $next($request);
    }
}
