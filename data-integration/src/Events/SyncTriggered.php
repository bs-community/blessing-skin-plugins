<?php
/**
 * @Author: prpr
 * @Date:   2016-11-06 12:07:43
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-31 14:04:33
 */

namespace DataIntegration\Events;

use App\Events\Event;
use DataIntegration\Synchronizer\BaseSynchronizer as Synchronizer;

class SyncTriggered extends Event
{
    public $synchronizer;

    public $username;

    public function __construct(Synchronizer $synchronizer, $username)
    {
        $this->synchronizer = $synchronizer;
        $this->username = $username;
    }
}
