<?php

namespace Blessing\ExamplePlugin\Listener;

use Illuminate\Contracts\Events\Dispatcher;

class SeparatedTestListener
{
    /**
     * 在这个方法里你可以做任何可以在 bootstrap.php 内做的事情
     *
     * @param  Dispatcher $events
     * @return mixed
     */
    public function subscribe(Dispatcher $events)
    {
        // do nothing
    }
}
