<?php
/**
 * @Author: printempw
 * @Date:   2017-01-14 21:24:53
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-20 21:27:16
 */

require 'bootstrap.php';

return [
    App\Events\PluginWasDeleted::class => function() {
        Schema::drop(RV_TABLE_NAME);
    }
];
