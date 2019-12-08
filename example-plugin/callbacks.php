<?php

return [
    App\Events\PluginWasEnabled::class => function (App\Services\PluginManager $manager, $plugins) {
        // 你也可以在回调函数的参数列表中使用类型提示，Laravel 服务容器将会自动进行依赖注入
        Log::info('[ExamplePlugin] 示例插件已启用，IoC 容器自动为我注入了 PluginManager 实例：', compact('manager'));
    },
    App\Events\PluginWasDisabled::class => function ($plugin) {
        // 回调函数被调用时 Plugin 实例会被传入作为参数
        Log::info('[ExamplePlugin] 示例插件已禁用，我拿到了插件实例', ['instance' => $plugin]);
    },
    App\Events\PluginWasDeleted::class => function () {
        Log::info('[ExamplePlugin] 啊啊啊啊啊啊啊我被删除啦 QwQ');
    }
];
