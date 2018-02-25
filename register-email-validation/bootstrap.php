<?php
/**
 * @Author: printempw
 * @Date:   2016-11-19 20:26:26
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-14 21:25:53
 */

use App\Events\PluginWasDeleted;
use RegisterValidation\Listener;
use Illuminate\Contracts\Events\Dispatcher;

define('RV_TABLE_NAME', 'register_validation');

return function (Dispatcher $events) {
    // create tables
    if (!Schema::hasTable(RV_TABLE_NAME)) {
        Schema::create(RV_TABLE_NAME, function($table) {
            $table->increments('uid');
            $table->string('token', 255);
            $table->integer('validated');
            $table->dateTime('last_sent_at');
            $table->dateTime('expired_at');
        });
    }

    // TODO:
    // 当用户修改邮箱时应该要求验证新邮箱有效性，否则可能会出现无法登录的情况。
    // 不过我现在没有时间就暂时搁置了，有缘再说吧。
    $events->subscribe(Listener\BanInvalidatedUser::class);
    $events->subscribe(Listener\AddValidationRoute::class);
    $events->subscribe(Listener\RefreshValidation::class);
};
