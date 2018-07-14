<?php

return [
    App\Events\PlayerWillBeDeleted::class => function () {
        // 删除插件后删除之前在 users 表添加的字段
        authme_deinit_table();
    }
];
