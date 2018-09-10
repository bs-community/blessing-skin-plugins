<?php
/**
 * @Author: printempw
 * @Date:   2016-12-10 18:58:54
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-10 19:31:02
 */
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(App\Events\RenderingHeader::class, function ($event) {
        $event->addContent('<meta name="keywords" content="'.option('meta_keywords').'" />');
        $event->addContent('<meta name="description" content="'.option('meta_description').'" />');
        $event->addContent(option('meta_extras'));
    });
};
