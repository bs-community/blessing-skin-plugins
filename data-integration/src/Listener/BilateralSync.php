<?php
/**
 * @Author: printempw
 * @Date:   2016-10-25 21:36:18
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 09:58:59
 */

namespace DataIntegration\Listener;

use Database;
use DataIntegration\Log;
use DataIntegration\Utils;
use DataIntegration\Synchronizer;
use DataIntegration\Events\SyncTriggerred;
use Illuminate\Contracts\Events\Dispatcher;

class BilateralSync
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        // 从皮肤站同步用户到目标程序（当「双向同步」开启时生效）
        $events->listen(SyncTriggerred::class, function($event) {
            // do bilateral sync
            if (Utils::checkUserExistSelf($event->username)) {
                // 当目标数据库不存在此用户时进行同步
                if (!Utils::checkUserExistTarget($event->username)) {

                    Log::info("[DataIntegration][$event->username][Self ==> Target] Sync triggerred with synchronizer: ".get_class($event->synchronizer));

                    $event->synchronizer->syncFromSelf($event->username);
                }
            } else {
                // do nothing if user not exists in blessing skin database
            }
        }, SynchronizeUser::PRIORITY);

    }
}
