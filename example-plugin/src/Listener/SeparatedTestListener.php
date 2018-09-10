<?php
/**
 * @Author: printempw
 * @Date:   2016-12-24 17:03:27
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-24 17:35:16
 */

namespace Blessing\ExamplePlugin\Listener;

use Illuminate\Contracts\Events\Dispatcher;

class SeparatedTestListener
{
    /**
     * 在这个方法里你可以做任何可以在 bootstrap.php 内做的事情.
     *
     * @param Dispatcher $events
     *
     * @return mixed
     */
    public function subscribe(Dispatcher $events)
    {
        // do nothing
    }
}
