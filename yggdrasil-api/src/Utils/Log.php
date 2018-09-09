<?php

namespace Yggdrasil\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
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
        $monolog = parent::__callStatic('channel', ['single']);
        $monolog->pushHandler(
            (new StreamHandler(static::getLogPath()))->setFormatter(
                new LineFormatter(null, null, true, true)
            )
        );

        // 仅当选项开启时记录日志
        if (menv('YGG_VERBOSE_LOG')) {
            return parent::__callStatic($method, $args);
        }
    }

    public static function getLogPath()
    {
        $dbConfig = config('database.connections.'.config('database.default'));
        $mask = substr(md5(implode(',', array_values($dbConfig))), 0, 8);

        return storage_path("logs/yggdrasil-$mask.log");
    }
}
