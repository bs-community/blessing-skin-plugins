<?php
/**
 * @Author: printempw
 * @Date:   2016-11-14 21:32:14
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-31 14:04:34
 */

namespace DataIntegration\Listener;

use App\Events\EncryptUserPassword;
use Illuminate\Contracts\Events\Dispatcher;

class EncryptPassword
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(EncryptUserPassword::class, function($event) {
            return app('synchronizer')->encryptPassword($event->rawPasswd, $event->user);
        });
    }
}
