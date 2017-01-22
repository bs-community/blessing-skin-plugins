<?php
/**
 * @Author: printempw
 * @Date:   2016-12-24 16:31:44
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-14 21:04:13
 */

use App\Services\Hook;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * 你可以在这个闭包的参数列表中使用类型提示
 * Laravel 会自动从容器中解析出对应的依赖并自动注入
 * 使用依赖注入之前你首先需要了解 Laravel 的服务容器机制
 *
 * 在这个闭包里你可以做任何准备工作，所有的代码都会在请求被处理之前执行
 * 包括不限于动态修改 config、修改 option、监听事件、绑定对象至服务容器等
 *
 * @see  https://laravel-china.org/docs/5.1/container
 */
return function (Dispatcher $events) {

    /**
     * 监听事件并通过传入的事件实例修改相应内容
     *
     * 如何监听事件：
     * $events->listen(string $className, callback $callback);
     *
     * 其中 $events 在上面通过依赖注入的 Dispatcher 实例，你可以通过它注册监听器
     * $className 是事件的完整类名，所有的事件均可在 app/Events 目录下找到，它们都已被映射至 App\Events 命名空间
     * $callback 是当事件被执行时你要执行的操作，必须是一个有效的 callback 类型，譬如一个闭包（匿名函数）
     *
     * @see  https://laravel-china.org/docs/5.1/events
     */
    $events->listen(App\Events\RenderingHeader::class, function($event) {
        /**
         * 以下代码的作用是在皮肤站的 <head> 标签内添加一些内容
         */
        $event->addContent('<script>console.info("这是示例插件通过 RenderingHeader 事件打印的内容");</script>');
    });

    // 你也可以使用这种方法在页面上加载 CSS/JS 文件
    Hook::addStyleFileToPage(plugin_assets('example-plugin', 'assets/css/example.css'));
    // plugin($pluginId)->assets($relativeUri) 是等价写法
    // 你可以在任何地方使用 plugin($pluginId) 拿到插件实例
    Hook::addScriptFileToPage(plugin('example-plugin')->assets('assets/js/example.js'));

    // 你也可以给 addStyleFileToPage 和 addScriptFileToPage 传递第二个参数
    // 该参数是一个包含字符串数组，只有符合其中定义的规则的页面才会被添加内容
    // 留空的话会在所有页面添加。规则中可以用 * 作为通配符。
    Hook::addScriptFileToPage(plugin('example-plugin')->assets('assets/js/example3.js'), [
        // 这样的话只有管理面板和皮肤库才会加载 example3.js 这个文件。打开 F12 看看吧~
        'admin*',
        'skinlib*'
    ]);

    // 第三个参数是优先级，越高越先加载
    Hook::addScriptFileToPage(plugin('example-plugin')->assets('assets/js/example4.js'), ['*'], 999);

    // 注册插件目录下用于 JavaScript 的语言文件
    Hook::registerPluginTransScripts('example-plugin');

    /**
     * 你也可以通过 subscribe 方法把代码分离到多个 Listener 中
     * 插件根目录下 src 目录中的类文件会被自动加载，你可以直接使用（通过命名空间加载）
     */
    $events->subscribe(Blessing\ExamplePlugin\Listener\SeparatedTestListener::class);

    /**
     * App\Services\Hook 这个类是 Blessing Skin 插件开发的帮助类
     * 目前提供了「添加菜单项」、「添加路由」等的便利方法
     * 具体使用方法请参考 Hook 类方法的注释
     *
     * 如何添加菜单项：
     * Hook::addMenuItem(string $category, int $position, array $menu);
     *
     * $category 的值只能为 "user" 或 "admin"，分别对应「用户中心」菜单和「管理界面」菜单
     * $position 是菜单项在整个菜单中的位置（从 0 开始），如果添加后看不到你的菜单，请检查这一项是否过大/过小
     * $menu 数组的值详见下
     */
    Hook::addMenuItem('user', 0, [
        // 菜单标题
        //
        // 这里 title 的定义使用了翻译器 translator
        // [Blessing\ExamplePlugin::general.menu] 中的命名空间表示在本插件 /lang 目录下寻找语言文件
        // [general.menu] 表示 general.yml 这个语言文件中的 [menu] 这个 key，可以多层嵌套下去
        // * 其他本地化的示例可以看下面的路由定义
        'title' => 'Blessing\ExamplePlugin::general.menu',
        // 菜单链接
        'link'  => 'user/example',
        // 菜单图标，详见 Font Awesome
        // @see http://fontawesome.io/icons/
        'icon'  => 'fa-gift'
    ]);

    /**
     * 如何添加一个路由：
     *
     * Hook::addRoute(callback $callback);
     *
     * $callback 必须是一个有效的 callback 类型值，如一个闭包、类似于 SomeClass@method 的字符串以及 [$instance, 'method'] 这样的数组
     * 你可以在这个闭包的参数列表中接受一个 $router，这是一个 Illuminate\Routing\Router 的实例，你可以通过它定义路由
     *
     * 注意：如果想要使用 Laravel 的路由缓存功能（php artisan route:cache）的话，请不要使用闭包路由。
     */
    Hook::addRoute(function ($router) {
        /**
         * 这只是一个最简单的路由定义（传递了闭包作为参数），其他用法请参照
         * @see  https://laravel-china.org/docs/5.1/routing
         */
        $router->get('/user/example', function() {
            // 改变浏览器/皮肤站的语言设置再访问此链接
            // 翻译器会依据你当前的语言设置显示对应的内容
            return trans('Blessing\ExamplePlugin::general.hello');
        })->middleware(['web', 'auth']);

        /**
         * 你也可以定义一个路由组，并指定组内路由的一些参数
         */
        $router->group([
            'middleware' => ['web', 'auth'],
            'namespace'  => 'Blessing\ExamplePlugin',
        ], function ($router) {
            /**
             * 你也可以传递一个类似 SomeController@someMethod 格式的值
             * 注意这里的 TestController 是定义在插件的 src 目录下的
             */
            $router->get('/user/welcome/{name}', 'TestController@welcome');
        });

    });
};
