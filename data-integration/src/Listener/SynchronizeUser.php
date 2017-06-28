<?php
/**
 * @Author: printempw
 * @Date:   2016-10-25 21:36:18
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-07 22:49:05
 */

namespace DataIntegration\Listener;

use App\Events;
use App\Models\User;
use DataIntegration\Log;
use DataIntegration\Utils;
use DataIntegration\Synchronizer;
use Illuminate\Contracts\Events\Dispatcher;
use DataIntegration\Synchronizer\ForumSynchronizer;

class SynchronizeUser
{
    /**
     * The priority of listeners.
     */
    const PRIORITY = 233;

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\CheckPlayerExists::class, function ($event) {
            Log::info("[DataIntegration] Triggered by event CheckPlayerExists");
            app('synchronizer')->sync($event->player_name);
        }, static::PRIORITY);

        $events->listen(Events\UserTryToLogin::class, function ($event) {
            Log::info("[DataIntegration] Triggered by event UserTryToLogin");
            $syncer = app('synchronizer');

            if ($event->authType == "username") {
                $syncer->sync($event->identification);
            } else {
                // 只有当对接到论坛时才会有 未注册过皮肤站的人 用论坛邮箱尝试登录皮肤站
                // 因此我们只需要处理这种情况就好了（通过邮箱从目标数据库获取用户名）
                $user = User::where('email', $event->identification)->first();

                // convert email to username for synchronizing
                if (!$user) {
                    // only try to sync when target table has email column
                    if ($syncer instanceof ForumSynchronizer) {
                        $syncer->sync(
                            Utils::getUsernameFromTargetByEmail($event->identification)
                        );
                    }
                } else {
                    $syncer->sync($user->username);
                }

            }
        }, static::PRIORITY);

        $events->listen(Events\PlayerProfileUpdated::class, function ($event) {
            Log::info("[DataIntegration] Triggered by event PlayerProfileUpdated");
            app('synchronizer')->sync($event->player->player_name);
        }, static::PRIORITY);

        $events->listen(Events\UserProfileUpdated::class, function ($event) {
            Log::info("[DataIntegration] Triggered by event UserProfileUpdated");
            if ($event->type == "email") {
                app('synchronizer')->sync($event->user->username);
            }
        }, static::PRIORITY);

        $events->listen(Events\UserRegistered::class, function ($event) {
            Log::info("[DataIntegration] Triggered by event UserRegistered");
            app('synchronizer')->sync($event->user->username);
        }, static::PRIORITY);
    }
}
