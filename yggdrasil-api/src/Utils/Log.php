<?php

namespace Yggdrasil\Utils;

use Illuminate\Support\Facades\Facade;

class Log extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'log';
    }

    public static function __callStatic($method, $args)
    {
        // only log when verbose log is enabled
        if (YGG_VERBOSE_LOG) {
            parent::__callStatic($method, $args);
        }
    }
}
