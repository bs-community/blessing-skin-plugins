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
        // 修改日志记录文件至 storage/logs/yggdrasil.log
        $monolog = parent::__callStatic('getMonolog', []);
        $monolog->popHandler();
        $monolog->pushHandler(
            (new StreamHandler(storage_path('logs/yggdrasil.log')))->setFormatter(
                new LineFormatter(null, null, true, true)
            )
        );

        // 仅当选项开启时记录日志
        if (option('ygg_verbose_log')) {
            return parent::__callStatic($method, $args);
        }
    }
}
