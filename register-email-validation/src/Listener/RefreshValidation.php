<?php

namespace RegisterValidation\Listener;

use DB;
use App\Events\UserProfileUpdated;
use Illuminate\Contracts\Events\Dispatcher;

class RefreshValidation
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(UserProfileUpdated::class, function($event) {
            if ($event->type == "email") {
                DB::table(RV_TABLE_NAME)->where('uid', $event->user->uid)->update(['validated' => '0']);
            }
        });
    }
}
