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
        if (option('ygg_verbose_log')) {
            parent::__callStatic($method, $args);
        }
    }
}
