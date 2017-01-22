<?php
/**
 * @Author: printempw
 * @Date:   2017-01-08 09:52:05
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-08 09:55:26
 */

namespace DataIntegration;

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
        if (option('da_verbose_log')) {
            parent::__callStatic($method, $args);
        }
    }
}
